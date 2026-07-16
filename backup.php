<?php
/**
 * AVISO DE SEGURANÇA: Apaga este ficheiro após o backup!
 */

// ─── CONFIGURAÇÕES ─────────────────────────────────
define('DB_HOST',     'localhost');
define('DB_USER',     'root');
define('DB_PASS',     '1234');
define('DB_NAME',     'sgf');
define('PROJECT_DIR', 'C:/xampp/htdocs/FARMA_LAMIA');   // pasta raiz do projecto
define('BACKUP_DIR',  'C:/xampp/htdocs/FARMA_LAMIA/backups'); // onde guardar
define('SECRET_KEY',  'sgf2026');                // chave de segurança (muda se quiseres)
// ────────────────────────────────────────────────────

session_start();
$action = $_GET['action'] ?? '';
$key    = $_GET['key']    ?? '';

// ─── SEGURANÇA MÍNIMA ────────────────────────────────
if ($key !== SECRET_KEY && $action !== '') {
    http_response_code(403);
    die('<p style="font-family:sans-serif;color:red">Acesso negado. Chave inválida.</p>');
}

// ─── FUNÇÕES AUXILIARES ──────────────────────────────

function criarDirBackup() {
    if (!is_dir(BACKUP_DIR)) {
        mkdir(BACKUP_DIR, 0755, true);
    }
}

function exportarSQL(): array {
    criarDirBackup();
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    if ($conn->connect_error) {
        return ['ok' => false, 'msg' => 'Erro de ligação: ' . $conn->connect_error];
    }
    $conn->set_charset('utf8mb4');

    $ficheiro = BACKUP_DIR . '/' . DB_NAME . '_' . date('Ymd_His') . '.sql';
    $sql      = "-- Backup SGF\n-- Data: " . date('Y-m-d H:i:s') . "\n-- Base de dados: " . DB_NAME . "\n\n";
    $sql     .= "SET FOREIGN_KEY_CHECKS=0;\n\n";

    $tabelas = $conn->query("SHOW TABLES");
    while ($row = $tabelas->fetch_row()) {
        $tabela = $row[0];

        // Estrutura
        $create = $conn->query("SHOW CREATE TABLE `$tabela`")->fetch_row();
        $sql   .= "DROP TABLE IF EXISTS `$tabela`;\n";
        $sql   .= $create[1] . ";\n\n";

        // Dados
        $dados = $conn->query("SELECT * FROM `$tabela`");
        if ($dados->num_rows > 0) {
            $sql .= "INSERT INTO `$tabela` VALUES\n";
            $linhas = [];
            while ($d = $dados->fetch_row()) {
                $vals = array_map(function($v) use ($conn) {
                    return is_null($v) ? 'NULL' : "'" . $conn->real_escape_string($v) . "'";
                }, $d);
                $linhas[] = '(' . implode(', ', $vals) . ')';
            }
            $sql .= implode(",\n", $linhas) . ";\n\n";
        }
    }

    $sql .= "SET FOREIGN_KEY_CHECKS=1;\n";
    $conn->close();

    file_put_contents($ficheiro, $sql);
    return ['ok' => true, 'ficheiro' => $ficheiro, 'nome' => basename($ficheiro), 'tamanho' => filesize($ficheiro)];
}

function exportarZIP(): array {
    criarDirBackup();

    if (!class_exists('ZipArchive')) {
        return ['ok' => false, 'msg' => 'Extensão ZipArchive não disponível no PHP.'];
    }

    $nomeFicheiro = 'sgf_projecto_' . date('Ymd_His') . '.zip';
    $ficheiro     = BACKUP_DIR . '/' . $nomeFicheiro;

    $zip = new ZipArchive();
    if ($zip->open($ficheiro, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
        return ['ok' => false, 'msg' => 'Não foi possível criar o ficheiro ZIP.'];
    }

    $dir    = realpath(PROJECT_DIR);
    $backup = realpath(BACKUP_DIR);

    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($dir, RecursiveDirectoryIterator::SKIP_DOTS),
        RecursiveIteratorIterator::LEAVES_ONLY
    );

    foreach ($iterator as $file) {
        $caminho = $file->getRealPath();
        // Ignora a própria pasta de backups para não criar loop
        if (strpos($caminho, $backup) === 0) continue;

        $relativo = substr($caminho, strlen($dir) + 1);
        $zip->addFile($caminho, $relativo);
    }

    $zip->close();
    return ['ok' => true, 'ficheiro' => $ficheiro, 'nome' => $nomeFicheiro, 'tamanho' => filesize($ficheiro)];
}

function formatarTamanho(int $bytes): string {
    if ($bytes >= 1048576) return round($bytes / 1048576, 2) . ' MB';
    if ($bytes >= 1024)    return round($bytes / 1024, 2) . ' KB';
    return $bytes . ' B';
}

function listarBackups(): array {
    if (!is_dir(BACKUP_DIR)) return [];
    $ficheiros = glob(BACKUP_DIR . '/*.{sql,zip}', GLOB_BRACE);
    usort($ficheiros, fn($a, $b) => filemtime($b) - filemtime($a));
    return $ficheiros;
}

// ─── ACÇÕES ──────────────────────────────────────────

if ($action === 'download' && isset($_GET['f'])) {
    $f = BACKUP_DIR . '/' . basename($_GET['f']);
    if (file_exists($f)) {
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . basename($f) . '"');
        header('Content-Length: ' . filesize($f));
        readfile($f);
        exit;
    }
    die('Ficheiro não encontrado.');
}

$resultado = null;
if ($action === 'sql') {
    $resultado = exportarSQL();
    $resultado['tipo'] = 'SQL';
}
if ($action === 'zip') {
    $resultado = exportarZIP();
    $resultado['tipo'] = 'ZIP do projecto';
}
if ($action === 'tudo') {
    $r1 = exportarSQL();
    $r2 = exportarZIP();
    $resultado = [
        'ok'   => $r1['ok'] && $r2['ok'],
        'tipo' => 'Backup completo',
        'r1'   => $r1,
        'r2'   => $r2,
    ];
}

$backups = listarBackups();
$urlBase = '?key=' . SECRET_KEY;
?>
<!DOCTYPE html>
<html lang="pt">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>SGF — Backup</title>
<style>
  * { box-sizing: border-box; margin: 0; padding: 0; }
  body { font-family: 'Segoe UI', sans-serif; background: #0d1b2a; color: #e2e8f0; min-height: 100vh; padding: 32px 16px; }
  .card { background: #162032; border: 1px solid #1e3a5f; border-radius: 12px; padding: 28px; max-width: 700px; margin: 0 auto 24px; }
  h1 { font-size: 22px; color: #60a5fa; margin-bottom: 4px; }
  .sub { font-size: 13px; color: #64748b; margin-bottom: 24px; }
  .btns { display: flex; gap: 12px; flex-wrap: wrap; margin-bottom: 8px; }
  .btn { padding: 10px 20px; border-radius: 8px; border: none; cursor: pointer; font-size: 14px; font-weight: 600; text-decoration: none; transition: opacity .15s; }
  .btn:hover { opacity: .85; }
  .btn-green  { background: #16a34a; color: #fff; }
  .btn-blue   { background: #2563eb; color: #fff; }
  .btn-purple { background: #7c3aed; color: #fff; }
  .btn-gray   { background: #334155; color: #cbd5e1; }
  .result { margin-top: 20px; padding: 16px; border-radius: 8px; font-size: 14px; }
  .ok  { background: #052e16; border: 1px solid #16a34a; color: #86efac; }
  .err { background: #1c0a0a; border: 1px solid #dc2626; color: #fca5a5; }
  table { width: 100%; border-collapse: collapse; font-size: 13px; margin-top: 12px; }
  th { text-align: left; padding: 8px 12px; background: #1e3a5f; color: #93c5fd; font-weight: 600; }
  td { padding: 8px 12px; border-top: 1px solid #1e3a5f; color: #cbd5e1; }
  .badge { display: inline-block; padding: 2px 8px; border-radius: 4px; font-size: 11px; font-weight: 600; }
  .badge-sql { background: #1e3a5f; color: #60a5fa; }
  .badge-zip { background: #2d1f5f; color: #c4b5fd; }
  .aviso { background: #2d1b00; border: 1px solid #d97706; border-radius: 8px; padding: 12px 16px; color: #fcd34d; font-size: 13px; margin-top: 20px; }
  hr { border: none; border-top: 1px solid #1e3a5f; margin: 20px 0; }
</style>
</head>
<body>
<div class="card">
  <h1>🗄 SGF — Backup do Sistema</h1>
  <p class="sub">Base de dados: <strong><?= DB_NAME ?></strong> &nbsp;|&nbsp; Projecto: <strong><?= PROJECT_DIR ?></strong></p>

  <div class="btns">
    <a class="btn btn-green"  href="<?= $urlBase ?>&action=sql">⬇ Exportar Base de Dados (.sql)</a>
    <a class="btn btn-blue"   href="<?= $urlBase ?>&action=zip">⬇ Exportar Projecto (.zip)</a>
    <a class="btn btn-purple" href="<?= $urlBase ?>&action=tudo">⚡ Backup Completo (sql + zip)</a>
  </div>

  <?php if ($resultado): ?>
    <?php if (isset($resultado['r1'])): // backup completo ?>
      <div class="result <?= ($resultado['r1']['ok'] ?? false) ? 'ok' : 'err' ?>">
        <strong>Base de dados:</strong>
        <?php if ($resultado['r1']['ok']): ?>
          ✅ <?= htmlspecialchars($resultado['r1']['nome']) ?> (<?= formatarTamanho($resultado['r1']['tamanho']) ?>)
          &nbsp;<a class="btn btn-gray" style="padding:4px 10px;font-size:12px" href="<?= $urlBase ?>&action=download&f=<?= urlencode($resultado['r1']['nome']) ?>">⬇ Download</a>
        <?php else: ?>
          ❌ <?= htmlspecialchars($resultado['r1']['msg']) ?>
        <?php endif; ?>
      </div>
      <div class="result <?= ($resultado['r2']['ok'] ?? false) ? 'ok' : 'err' ?>" style="margin-top:8px">
        <strong>Projecto ZIP:</strong>
        <?php if ($resultado['r2']['ok']): ?>
          ✅ <?= htmlspecialchars($resultado['r2']['nome']) ?> (<?= formatarTamanho($resultado['r2']['tamanho']) ?>)
          &nbsp;<a class="btn btn-gray" style="padding:4px 10px;font-size:12px" href="<?= $urlBase ?>&action=download&f=<?= urlencode($resultado['r2']['nome']) ?>">⬇ Download</a>
        <?php else: ?>
          ❌ <?= htmlspecialchars($resultado['r2']['msg']) ?>
        <?php endif; ?>
      </div>
    <?php else: ?>
      <div class="result <?= $resultado['ok'] ? 'ok' : 'err' ?>">
        <?php if ($resultado['ok']): ?>
          ✅ <strong><?= $resultado['tipo'] ?></strong> criado com sucesso:
          <?= htmlspecialchars($resultado['nome']) ?>
          (<?= formatarTamanho($resultado['tamanho']) ?>)
          &nbsp;<a class="btn btn-gray" style="padding:4px 10px;font-size:12px" href="<?= $urlBase ?>&action=download&f=<?= urlencode($resultado['nome']) ?>">⬇ Download</a>
        <?php else: ?>
          ❌ Erro: <?= htmlspecialchars($resultado['msg']) ?>
        <?php endif; ?>
      </div>
    <?php endif; ?>
  <?php endif; ?>

  <div class="aviso">
    ⚠️ <strong>Segurança:</strong> Apaga este ficheiro <code>backup.php</code> após terminar o backup.
    Não o deixes acessível em produção.
  </div>
</div>

<?php if (!empty($backups)): ?>
<div class="card">
  <h1 style="font-size:17px">📁 Backups gerados</h1>
  <table>
    <tr><th>Ficheiro</th><th>Tipo</th><th>Tamanho</th><th>Data</th><th></th></tr>
    <?php foreach ($backups as $f): ?>
    <tr>
      <td><?= htmlspecialchars(basename($f)) ?></td>
      <td>
        <?php $ext = pathinfo($f, PATHINFO_EXTENSION); ?>
        <span class="badge badge-<?= $ext ?>">.<?= strtoupper($ext) ?></span>
      </td>
      <td><?= formatarTamanho(filesize($f)) ?></td>
      <td><?= date('d/m/Y H:i', filemtime($f)) ?></td>
      <td><a class="btn btn-gray" style="padding:4px 10px;font-size:12px" href="<?= $urlBase ?>&action=download&f=<?= urlencode(basename($f)) ?>">⬇ Download</a></td>
    </tr>
    <?php endforeach; ?>
  </table>
</div>
<?php endif; ?>

</body>
</html>
