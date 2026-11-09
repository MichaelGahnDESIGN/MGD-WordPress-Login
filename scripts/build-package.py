"""Plugin-Paket (ZIP) bauen, ohne Werkzeuge, Tests oder private Dateien."""
from pathlib import Path
from hashlib import sha256
from zipfile import ZipFile, ZIP_DEFLATED
import json
import re

root = Path(__file__).resolve().parents[1]
plugin = root / 'mgd-wordpress-login'
version = re.search(r"Version:\s*([0-9.]+)", (plugin / 'mgd-wordpress-login.php').read_text(encoding='utf-8')).group(1)
output = root / 'artifacts'
output.mkdir(exist_ok=True)
archive = output / f'mgd-wordpress-login-{version}.zip'
files = sorted(p for p in plugin.rglob('*') if p.is_file() and not any(part.startswith('.') for part in p.relative_to(plugin).parts))
allowed = {'.php', '.css', '.js', '.json', '.md', '.txt', '.svg', '.png', '.webp'}
assert files and all(not p.is_symlink() and (p.suffix in allowed or p.name == 'LICENSE') for p in files)
manifest = {}
with ZipFile(archive, 'w', ZIP_DEFLATED) as bundle:
    for path in files:
        name = str(path.relative_to(root))
        content = path.read_bytes()
        bundle.writestr(name, content)
        manifest[name] = sha256(content).hexdigest()
with ZipFile(archive) as bundle:
    assert bundle.testzip() is None
    assert set(bundle.namelist()) == set(manifest)
    for name, digest in manifest.items():
        assert sha256(bundle.read(name)).hexdigest() == digest
(output / 'SHA256SUMS.txt').write_text(sha256(archive.read_bytes()).hexdigest() + '  ' + archive.name + '\n', encoding='utf-8')
print(f'{archive.name}: {len(files)} Dateien, Inhalt und Prüfsummen bestätigt')
