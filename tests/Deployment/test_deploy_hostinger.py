import json, os, pathlib, subprocess, tempfile, unittest
PROJECT = pathlib.Path(__file__).resolve().parents[2]
SCRIPT = PROJECT / 'scripts/deploy-hostinger.sh'
PACKAGE = PROJECT / 'scripts/package-hostinger.sh'
MOCK = '''#!/usr/bin/python3
import json,os,pathlib,subprocess,sys
name=pathlib.Path(sys.argv[0]).name
args=sys.argv[1:]
if name=='rsync' and '-e' not in args:
 sys.exit(subprocess.run(['/usr/bin/rsync',*args]).returncode)
data=sys.stdin.read() if name=='ssh' else ''
entry={'name':name,'args':args,'stdin':data}
if name=='rsync':
 root=pathlib.Path(args[-2]); entry['files']=sorted(str(p.relative_to(root)) for p in root.rglob('*') if p.is_file())
with open(os.environ['TEST_LOG'],'a') as f: f.write(json.dumps(entry)+'\\n')
if name=='rsync' and os.environ.get('FAIL_TRANSFER')=='1': sys.exit(1)
'''
class Deployment(unittest.TestCase):
 def run_case(self, mode='preview', fail_transfer=False, missing=False):
  with tempfile.TemporaryDirectory() as tmp:
   root=pathlib.Path(tmp)
   files=['vendor/autoload.php','public/assets/manifest.json','public/assets/styles/portfolio.css',
    'public/uploads/private.mp3','public/.htaccess','public/router.php','public/index.php',
    'assets/app.js','bin/console','config/services.yaml','config/.env.secret','migrations/a.php',
    'src/Kernel.php','templates/base.html.twig','translations/messages.en.yaml',
    'composer.json','composer.lock','symfony.lock','importmap.php']
   for file in files:
    path=root/file;path.parent.mkdir(parents=True,exist_ok=True);path.write_text('fixture')
   if not missing:
    subprocess.run(['bash', str(PACKAGE)], cwd=root, check=True, capture_output=True, text=True)
   mock=root/'mocks';mock.mkdir()
   for name in ['ssh','rsync','curl']:
    path=mock/name;path.write_text(MOCK);path.chmod(0o755)
   log=root/'calls.jsonl'
   env={**os.environ,'PATH':str(mock)+':'+os.environ['PATH'],'SSH_DIRECTORY':str(root/'ssh'),
    'GITHUB_SHA':'a'*40,'DEPLOY_MODE':mode,'TEST_LOG':str(log),'FAIL_TRANSFER':str(int(fail_transfer))}
   result=subprocess.run(['bash',str(SCRIPT)],cwd=root,env=env,capture_output=True,text=True)
   calls=[json.loads(line) for line in log.read_text().splitlines()] if log.exists() else []
   return result,calls
 def test_preview(self):
  result,calls=self.run_case();self.assertEqual(result.returncode,0,result.stderr)
  self.assertEqual([c['name'] for c in calls],['ssh','rsync'])
  transfer=calls[1];self.assertIn('--dry-run',transfer['args'])
  self.assertFalse(any('--delete' in a for a in transfer['args']))
  for path in ['public/uploads/private.mp3','public/.htaccess','public/router.php','config/.env.secret']:
   self.assertNotIn(path,transfer['files'])
  self.assertIn('public/assets/styles/portfolio.css',transfer['files'])
  self.assertIn('vendor/autoload.php',transfer['files'])
 def test_deploy_without_backup(self):
  result,calls=self.run_case('deploy');self.assertEqual(result.returncode,0,result.stderr)
  self.assertEqual([c['name'] for c in calls[:3]],['ssh','rsync','ssh'])
  self.assertNotIn('--dry-run',calls[1]['args'])
  self.assertIn('cache:clear',calls[2]['stdin'])
  for call in calls:
   self.assertNotIn('tar -czf',call['stdin'])
   self.assertNotIn('deploy-backups',call['stdin'])
  self.assertEqual(sum(c['name']=='curl' for c in calls),5)
 def test_failed_transfer_blocks_cache_and_http_checks(self):
  result,calls=self.run_case('deploy',True);self.assertNotEqual(result.returncode,0)
  self.assertEqual([c['name'] for c in calls],['ssh','rsync'])
 def test_invalid_mode(self):
  result,calls=self.run_case('invalid');self.assertNotEqual(result.returncode,0);self.assertEqual(calls,[])
 def test_missing_build_blocks_connection(self):
  result,calls=self.run_case(missing=True);self.assertNotEqual(result.returncode,0);self.assertEqual(calls,[])
if __name__=='__main__':unittest.main()
