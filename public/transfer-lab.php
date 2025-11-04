<?php

declare(strict_types=1);

require_once __DIR__ . '/includes/bootstrap.php';

$uploadDir = __DIR__ . '/../uploads';
if (!is_dir($uploadDir) && !mkdir($uploadDir, 0775, true)) {
    redirect_with_status('transfer-lab.php', 'danger', 'Répertoire d\'upload inaccessible.');
}
$uploadDirReal = realpath($uploadDir);
if ($uploadDirReal !== false) {
    $uploadDir = $uploadDirReal;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'upload') {
        $file = $_FILES['file'] ?? null;

        if (!$file || !isset($file['error']) || $file['error'] !== UPLOAD_ERR_OK) {
            redirect_with_status('transfer-lab.php', 'danger', 'Erreur lors de l\'upload (code ' . ($file['error'] ?? 'n/a') . ').');
        }

        if (!is_uploaded_file($file['tmp_name'] ?? '')) {
            redirect_with_status('transfer-lab.php', 'danger', 'Le fichier reçu n\'est pas un upload valide.');
        }

        if (($file['size'] ?? 0) > MAX_UPLOAD_SIZE) {
            redirect_with_status(
                'transfer-lab.php',
                'warning',
                'Fichier trop volumineux. Limite fixée à ' . number_format(MAX_UPLOAD_SIZE / 1024 / 1024, 2) . ' MB.'
            );
        }

        $originalName = $file['name'] ?? 'fichier';
        $safeName = preg_replace('/[^a-zA-Z0-9._-]/', '-', $originalName);
        $safeName = ltrim($safeName, '.'); // avoid hidden dotfiles
        if ($safeName === '') {
            $safeName = 'upload';
        }

        $extension = strtolower((string) pathinfo($safeName, PATHINFO_EXTENSION));
        if ($extension !== '' && in_array($extension, DISALLOWED_UPLOAD_EXTENSIONS, true)) {
            redirect_with_status('transfer-lab.php', 'danger', 'Extension de fichier "' . htmlspecialchars($extension, ENT_QUOTES, 'UTF-8') . '" interdite pour des raisons de sécurité.');
        }

        $targetName = date('Ymd-His') . '-' . $safeName;
        $targetPath = $uploadDir . DIRECTORY_SEPARATOR . $targetName;

        if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
            redirect_with_status('transfer-lab.php', 'danger', 'Impossible d\'écrire le fichier (droits ?).');
        }

        redirect_with_status('transfer-lab.php', 'success', 'Fichier "' . $originalName . '" uploadé avec succès.');
    }
}

$uploadedFiles = [];
if (is_dir($uploadDir)) {
    $iterator = new DirectoryIterator($uploadDir);
    foreach ($iterator as $fileinfo) {
        if ($fileinfo->isDot() || !$fileinfo->isFile()) {
            continue;
        }

        $uploadedFiles[] = [
            'name' => $fileinfo->getFilename(),
            'size' => $fileinfo->getSize(),
            'mtime' => $fileinfo->getMTime(),
        ];
    }
}

render_header('Transferts', 'transfer');
?>
<div class="row g-4">
    <div class="col-12 col-lg-6">
        <div class="card shadow-sm h-100">
            <div class="card-body">
                <h1 class="h4 mb-3">Upload de fichier</h1>
                <p class="text-muted small">
                    Téléchargez un fichier pour vérifier la taille, l'encodage multipart et l'écriture disque via le proxy.
                </p>
                <form method="post" enctype="multipart/form-data" class="row g-3">
                    <input type="hidden" name="action" value="upload">
                    <div class="col-12">
                        <label class="form-label" for="file">Choisir un fichier</label>
                        <input type="file" class="form-control" id="file" name="file" required>
                    </div>
                    <div class="col-12">
                        <button type="submit" class="btn btn-primary w-100">Uploader</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <div class="col-12 col-lg-6">
        <div class="card shadow-sm h-100">
            <div class="card-body">
                <h2 class="h5">Download statique</h2>
                <p class="text-muted small">
                    Utilisez ce fichier pour vérifier que le reverse proxy conserve les entêtes HTTP et l'intégrité du contenu.
                </p>
                <a href="download.php?file=proxy-test.txt" class="btn btn-outline-secondary">Télécharger le fichier de test</a>
                <p class="mt-3 mb-0 small text-muted">
                    Contrôlez la taille, comparez les checksums et observez les entêtes (Content-Type, Content-Length, Cache-Control).
                </p>
            </div>
        </div>
    </div>
</div>

<div class="card shadow-sm mt-4">
    <div class="card-body">
        <h2 class="h5">Fichiers uploadés</h2>
        <?php if (empty($uploadedFiles)): ?>
            <p class="text-muted">Aucun fichier n'a encore été déposé.</p>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-sm align-middle">
                    <thead>
                    <tr>
                        <th>Nom</th>
                        <th>Taille</th>
                        <th>Uploadé le</th>
                        <th></th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($uploadedFiles as $file): ?>
                        <tr>
                            <td><code><?= htmlspecialchars($file['name']) ?></code></td>
                            <td><?= number_format((float) $file['size'] / 1024, 2) ?> KB</td>
                            <td><?= date('Y-m-d H:i:s', (int) $file['mtime']) ?></td>
                            <td class="text-end">
                                <a class="btn btn-outline-primary btn-sm" href="download-upload.php?file=<?= urlencode($file['name']) ?>">Télécharger</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>
<?php
render_footer();
