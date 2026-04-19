<?php
require "funcoes.php";

// POST: Guardar alterações
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    editarContacto(
        $_POST['id'],
        $_POST['primeiro_nome'],
        $_POST['apelido'],
        $_POST['email'],
        $_POST['telefone'],
        $_POST['cargo'],
        $_POST['empresa'],
        $_POST['categoria'],
        $_POST['cidade']
    );
    header("Location: /Laboratorio");
    exit;
}

// GET: Mostrar formulário
$idContacto = $_GET['id'] ?? null;
if (!$idContacto) { header("Location: /Laboratorio"); exit; }

$contacto = null;
foreach (listarContactos() as $c) {
    if ($c['id'] === $idContacto) { $contacto = $c; break; }
}
if (!$contacto) { header("Location: /Laboratorio"); exit; }
?>
<!DOCTYPE html>
<html lang="pt-pt" data-bs-theme="dark">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Editar · <?= htmlspecialchars($contacto['nome'].' '.$contacto['apelido']) ?></title>
  <link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=Epilogue:ital,wght@0,300;0,400;0,500;0,700;1,300;1,400&display=swap" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
  <style>
    :root {
      --lime:     #C8F135;
      --lime-dim: #a8cc28;
      --bg:       #0f0f10;
      --surface:  #18181a;
      --surface2: #202023;
      --border:   #2a2a2e;
      --fg:       #e8e8e2;
      --muted:    #6b6b72;
    }

    body {
      background: var(--bg);
      color: var(--fg);
      font-family: 'Epilogue', sans-serif;
      min-height: 100vh;
      display: flex;
      flex-direction: column;
    }

    .ticker {
      background: var(--lime);
      color: #0f0f10;
      font-family: 'Bebas Neue', sans-serif;
      font-size: .85rem;
      letter-spacing: .18em;
      text-align: center;
      padding: 6px 0;
    }

    .masthead {
      background: var(--surface);
      border-bottom: 2px solid var(--lime);
      padding: 20px 32px 16px;
    }
    .mast-issue { font-size: .63rem; letter-spacing: .22em; color: var(--muted); text-transform: uppercase; margin-bottom: 4px; }
    .mast-title { font-family: 'Bebas Neue', sans-serif; font-size: clamp(2rem, 4vw, 3rem); line-height: 1; letter-spacing: .04em; }
    .mast-title span { color: var(--lime); }

    /* Breadcrumb personalizado */
    .breadcrumb-bar {
      background: var(--surface2);
      border-bottom: 1px solid var(--border);
      padding: 10px 32px;
      display: flex;
      align-items: center;
      gap: 8px;
      font-size: .72rem;
      letter-spacing: .08em;
      color: var(--muted);
    }
    .breadcrumb-bar a { color: var(--muted); text-decoration: none; transition: color .15s; }
    .breadcrumb-bar a:hover { color: var(--lime); }
    .breadcrumb-bar .bi-chevron-right { font-size: .6rem; }
    .breadcrumb-bar .current { color: var(--fg); }

    /* Card central */
    .edit-wrap {
      flex: 1;
      display: flex;
      align-items: flex-start;
      justify-content: center;
      padding: 40px 16px 60px;
    }
    .edit-card {
      width: 100%;
      max-width: 620px;
      background: var(--surface);
      border: 1px solid var(--border);
      border-radius: 8px;
      overflow: hidden;
    }
    .edit-card-head {
      background: var(--surface2);
      border-bottom: 1px solid var(--border);
      padding: 14px 20px;
    }
    .edit-card-title { font-family: 'Bebas Neue', sans-serif; font-size: 1rem; letter-spacing: .1em; }
    .edit-card-sub   { font-size: .72rem; color: var(--lime); margin-top: 1px; }

    /* Photo zone */
    .photo-zone {
      display: flex;
      align-items: center;
      gap: 16px;
      padding: 16px 20px;
      border-bottom: 1px solid var(--border);
      background: var(--surface2);
    }
    .photo-av {
      width: 64px; height: 64px;
      border-radius: 50%;
      border: 2px dashed var(--border);
      background: var(--surface);
      display: flex; align-items: center; justify-content: center;
      overflow: hidden;
      cursor: pointer;
      flex-shrink: 0;
      transition: border-color .2s;
    }
    .photo-av:hover { border-color: var(--lime); }
    .photo-av img { width: 100%; height: 100%; object-fit: cover; }
    .photo-av .bi { color: var(--muted); font-size: 1.4rem; }
    .photo-info-label { font-size: .65rem; letter-spacing: .1em; text-transform: uppercase; color: var(--fg); font-weight: 600; }
    .photo-info-sub   { font-size: .7rem; color: var(--muted); line-height: 1.6; }

    /* Form body */
    .edit-body { padding: 20px; }
    .edit-body label {
      font-size: .65rem;
      letter-spacing: .12em;
      text-transform: uppercase;
      color: var(--muted);
      margin-bottom: 4px;
      display: block;
    }
    .edit-body .form-control,
    .edit-body .form-select {
      background: var(--surface2);
      border: 1px solid var(--border);
      color: var(--fg);
      font-family: 'Epilogue', sans-serif;
      font-size: .83rem;
      border-radius: 4px;
    }
    .edit-body .form-control:focus,
    .edit-body .form-select:focus {
      background: var(--surface2);
      border-color: var(--lime);
      color: var(--fg);
      box-shadow: 0 0 0 3px rgba(200,241,53,.12);
    }
    .edit-body .form-select option { background: #202023; }
    .edit-body .form-control::placeholder { color: var(--muted); }

    /* Footer card */
    .edit-foot {
      display: flex;
      justify-content: flex-end;
      gap: 10px;
      padding: 14px 20px;
      border-top: 1px solid var(--border);
      background: var(--surface2);
    }
    .btn-cancel {
      background: transparent;
      border: 1px solid var(--border);
      color: var(--muted);
      font-family: 'Epilogue', sans-serif;
      font-size: .8rem;
      padding: 7px 18px;
      border-radius: 4px;
      text-decoration: none;
      transition: all .15s;
    }
    .btn-cancel:hover { border-color: var(--muted); color: var(--fg); }
    .btn-save {
      background: var(--lime);
      color: #0f0f10;
      border: none;
      font-family: 'Bebas Neue', sans-serif;
      font-size: .85rem;
      letter-spacing: .1em;
      padding: 7px 22px;
      border-radius: 4px;
      cursor: pointer;
      display: inline-flex;
      align-items: center;
      gap: 5px;
      transition: background .15s;
    }
    .btn-save:hover { background: var(--lime-dim); color: #0f0f10; }

    /* Footer page */
    footer {
      background: var(--surface);
      border-top: 1px solid var(--border);
      padding: 12px 32px;
      display: flex;
      justify-content: space-between;
      align-items: center;
      flex-wrap: wrap;
      gap: 8px;
      font-size: .63rem;
      letter-spacing: .14em;
      color: var(--muted);
      text-transform: uppercase;
    }

    @media (max-width: 576px) {
      .masthead, .breadcrumb-bar, footer { padding-left: 16px; padding-right: 16px; }
    }
  </style>
</head>
<body>

  <div class="ticker">Capacita CFTI · Formação Inicial de Desenvolvimento Backend</div>

  <header class="masthead">
    <div class="mast-issue">Edição Especial · Directório Pessoal</div>
    <div class="mast-title">LISTA DE <span>CONTACTOS</span></div>
  </header>

  <!-- BREADCRUMB -->
  <nav class="breadcrumb-bar">
    <a href="/Laboratorio"><i class="bi bi-house me-1"></i>Início</a>
    <i class="bi bi-chevron-right"></i>
    <span class="current">
      Editar — <?= htmlspecialchars($contacto['nome'].' '.$contacto['apelido']) ?>
    </span>
  </nav>

  <!-- FORMULÁRIO -->
  <div class="edit-wrap">
    <div class="edit-card">

      <div class="edit-card-head">
        <div class="edit-card-title">Editar Contacto</div>
        <div class="edit-card-sub">
          <?= htmlspecialchars($contacto['nome'].' '.$contacto['apelido']) ?>
        </div>
      </div>

      <form action="editar-contacto.php" method="POST" enctype="multipart/form-data">
        <input type="hidden" name="id" value="<?= htmlspecialchars($contacto['id']) ?>">

        <!-- Foto -->
        <div class="photo-zone">
          <div class="photo-av" id="editPhotoAv"
               onclick="document.getElementById('editFotoInput').click()"
               title="Clique para alterar a foto">
            <?php if ($contacto['foto']): ?>
              <img id="editPhotoPreview"
                   src="uploads/<?= htmlspecialchars(trim($contacto['foto'])) ?>"
                   alt=""
                   onerror="this.src='';this.style.display='none';document.getElementById('editPhotoIcon').style.display='block'">
            <?php else: ?>
              <i class="bi bi-person" id="editPhotoIcon"></i>
            <?php endif; ?>
          </div>
          <div>
            <div class="photo-info-label">Foto de Perfil</div>
            <div class="photo-info-sub">
              Deixe em branco para manter a foto actual<br>
              JPG, PNG · Máx. 5 MB
            </div>
            <input type="file" name="foto" id="editFotoInput" accept="image/*" class="d-none">
          </div>
        </div>

        <!-- Campos -->
        <div class="edit-body">
          <div class="row g-3">
            <div class="col-sm-6">
              <label>Primeiro Nome *</label>
              <input type="text" name="primeiro_nome" class="form-control"
                     value="<?= htmlspecialchars($contacto['nome']) ?>" required>
            </div>
            <div class="col-sm-6">
              <label>Apelido *</label>
              <input type="text" name="apelido" class="form-control"
                     value="<?= htmlspecialchars($contacto['apelido']) ?>" required>
            </div>
            <div class="col-12">
              <label>Email</label>
              <input type="email" name="email" class="form-control"
                     value="<?= htmlspecialchars($contacto['email']) ?>">
            </div>
            <div class="col-sm-6">
              <label>Telefone</label>
              <input type="tel" name="telefone" class="form-control"
                     value="<?= htmlspecialchars($contacto['telefone']) ?>">
            </div>
            <div class="col-sm-6">
              <label>Categoria</label>
              <select name="categoria" class="form-select">
                <option value="trabalho" <?= trim($contacto['categoria'])==='trabalho'?'selected':'' ?>>Trabalho</option>
                <option value="pessoal"  <?= trim($contacto['categoria'])==='pessoal' ?'selected':'' ?>>Pessoal</option>
              </select>
            </div>
            <div class="col-sm-6">
              <label>Cargo</label>
              <input type="text" name="cargo" class="form-control"
                     value="<?= htmlspecialchars($contacto['cargo']) ?>">
            </div>
            <div class="col-sm-6">
              <label>Empresa</label>
              <input type="text" name="empresa" class="form-control"
                     value="<?= htmlspecialchars($contacto['empresa']) ?>">
            </div>
            <div class="col-12">
              <label>Cidade</label>
              <input type="text" name="cidade" class="form-control"
                     value="<?= htmlspecialchars($contacto['cidade']) ?>">
            </div>
          </div>
        </div>

        <div class="edit-foot">
          <a class="btn-cancel" href="/Laboratorio">
            <i class="bi bi-arrow-left me-1"></i> Cancelar
          </a>
          <button type="submit" class="btn-save">
            <i class="bi bi-floppy"></i> Guardar Alterações
          </button>
        </div>
      </form>

    </div>
  </div>

  <footer>
    <span>Lista de Contactos · Directório Pessoal</span>
    <span><?= date('d \d\e F \d\e Y') ?></span>
  </footer>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    document.getElementById('editFotoInput').addEventListener('change', function () {
      const file = this.files[0];
      if (!file) return;
      const reader = new FileReader();
      reader.onload = e => {
        let img = document.getElementById('editPhotoPreview');
        if (!img) {
          img = document.createElement('img');
          img.id = 'editPhotoPreview';
          img.style.cssText = 'width:100%;height:100%;object-fit:cover';
          document.getElementById('editPhotoAv').appendChild(img);
        }
        img.src = e.target.result;
        img.style.display = 'block';
        const icon = document.getElementById('editPhotoIcon');
        if (icon) icon.style.display = 'none';
      };
      reader.readAsDataURL(file);
    });
  </script>
</body>
</html>
