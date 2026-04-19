<?php
require "funcoes.php";

$query     = trim($_GET["q"] ?? "");
$categoria = trim($_GET["cat"] ?? "");   // filtro por categoria
$contactos = $query !== "" ? pesquisarContactos($query) : listarContactos();

// Filtro de categoria (server-side, combinável com pesquisa)
if ($categoria !== "") {
    $contactos = array_filter($contactos, fn($c) => trim($c['categoria']) === $categoria);
    $contactos = array_values($contactos);
}

$total    = count(listarContactos());
$trabalho = count(array_filter(listarContactos(), fn($c) => trim($c['categoria']) === 'trabalho'));
$pessoal  = count(array_filter(listarContactos(), fn($c) => trim($c['categoria']) === 'pessoal'));
?>
<!DOCTYPE html>
<html lang="pt-pt" data-bs-theme="dark">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Meus Contactos</title>
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
      --danger:   #ff4d4d;
    }
    body {
      background: var(--bg); color: var(--fg);
      font-family: 'Epilogue', sans-serif;
      min-height: 100vh; display: flex; flex-direction: column;
    }

    /* Ticker */
    .ticker {
      background: var(--lime); color: #0f0f10;
      font-family: 'Bebas Neue', sans-serif;
      font-size: .85rem; letter-spacing: .18em;
      text-align: center; padding: 6px 0;
    }

    /* Masthead */
    .masthead {
      background: var(--surface);
      border-bottom: 2px solid var(--lime);
      padding: 24px 32px 20px;
    }
    .mast-issue { font-size: .63rem; letter-spacing: .22em; color: var(--muted); text-transform: uppercase; margin-bottom: 4px; }
    .mast-title { font-family: 'Bebas Neue', sans-serif; font-size: clamp(2.2rem, 5vw, 3.6rem); line-height: 1; letter-spacing: .04em; }
    .mast-title span { color: var(--lime); }
    .stat-card { background: var(--surface2); border: 1px solid var(--border); border-radius: 6px; padding: 10px 20px; text-align: center; cursor: pointer; transition: border-color .15s; text-decoration: none; }
    .stat-card:hover, .stat-card.active { border-color: var(--lime); }
    .stat-card.active-pessoal { border-color: var(--muted); }
    .stat-n { font-family: 'Bebas Neue', sans-serif; font-size: 2rem; line-height: 1; }
    .stat-l { font-size: .58rem; letter-spacing: .14em; color: var(--muted); text-transform: uppercase; margin-top: 2px; }

    /* Toolbar */
    .toolbar {
      background: var(--surface2); border-bottom: 1px solid var(--border);
      padding: 12px 32px; display: flex; align-items: center; gap: 10px; flex-wrap: wrap;
    }

    /* Search */
    .search-wrap { position: relative; flex: 1; min-width: 200px; max-width: 420px; }
    .search-wrap .bi-search {
      position: absolute; left: 11px; top: 50%; transform: translateY(-50%);
      color: var(--muted); font-size: .82rem; pointer-events: none;
      transition: color .2s;
    }
    .search-wrap:focus-within .bi-search { color: var(--lime); }
    .search-input {
      background: var(--surface); border: 1px solid var(--border); color: var(--fg);
      border-radius: 4px; padding: 8px 36px 8px 32px;
      font-family: 'Epilogue', sans-serif; font-size: .82rem; width: 100%;
      transition: border-color .2s, box-shadow .2s;
    }
    .search-input::placeholder { color: var(--muted); }
    .search-input:focus { outline: none; border-color: var(--lime); box-shadow: 0 0 0 3px rgba(200,241,53,.1); }

    /* Clear button inside input */
    .btn-clear-search {
      position: absolute; right: 8px; top: 50%; transform: translateY(-50%);
      background: none; border: none; color: var(--muted); font-size: .8rem;
      cursor: pointer; padding: 2px 4px; border-radius: 3px;
      display: none; line-height: 1; transition: color .15s;
    }
    .btn-clear-search:hover { color: var(--fg); }

    /* Live indicator */
    .live-badge {
      font-size: .6rem; letter-spacing: .1em; text-transform: uppercase;
      background: rgba(200,241,53,.12); color: var(--lime);
      border: 1px solid rgba(200,241,53,.25);
      padding: 2px 8px; border-radius: 3px;
      display: none; align-items: center; gap: 4px; white-space: nowrap;
    }
    .live-dot {
      width: 5px; height: 5px; border-radius: 50%;
      background: var(--lime);
      animation: pulse 1.2s ease-in-out infinite;
    }
    @keyframes pulse { 0%,100%{opacity:1} 50%{opacity:.3} }

    /* Filter chips */
    .filter-chips { display: flex; gap: 6px; flex-wrap: wrap; }
    .chip {
      font-family: 'Bebas Neue', sans-serif;
      font-size: .72rem; letter-spacing: .1em;
      padding: 4px 12px; border-radius: 20px;
      border: 1px solid var(--border); background: transparent; color: var(--muted);
      cursor: pointer; text-decoration: none;
      transition: all .15s; white-space: nowrap;
    }
    .chip:hover               { border-color: var(--fg); color: var(--fg); }
    .chip.active-all           { border-color: var(--lime); color: var(--lime); background: rgba(200,241,53,.08); }
    .chip.active-trabalho      { border-color: var(--lime); color: var(--lime); background: rgba(200,241,53,.08); }
    .chip.active-pessoal       { border-color: var(--muted); color: var(--muted); background: rgba(107,107,114,.1); }

    /* Results info bar */
    .results-bar {
      background: var(--bg);
      border-bottom: 1px solid var(--border);
      padding: 7px 32px;
      display: flex; align-items: center; justify-content: space-between;
      font-size: .7rem; color: var(--muted); letter-spacing: .06em;
      flex-wrap: wrap; gap: 6px;
    }
    .results-count strong { color: var(--fg); }
    .results-query {
      background: var(--surface2); border: 1px solid var(--border);
      border-radius: 3px; padding: 1px 8px;
      font-family: 'Bebas Neue', sans-serif; font-size: .72rem; letter-spacing: .08em;
      color: var(--lime);
    }

    /* Table */
    .table-wrap { flex: 1; overflow-x: auto; padding-bottom: 32px; }
    table.ct { width: 100%; border-collapse: collapse; font-size: .82rem; }
    .ct thead th {
      background: var(--surface2); color: var(--muted);
      font-family: 'Bebas Neue', sans-serif; font-size: .73rem; letter-spacing: .14em;
      padding: 11px 14px; border-bottom: 1px solid var(--border); white-space: nowrap;
    }
    .ct thead th a { color: var(--muted); text-decoration: none; }
    .ct thead th a:hover { color: var(--lime); }
    .ct tbody tr { border-bottom: 1px solid var(--border); transition: background .12s; }
    .ct tbody tr:hover { background: var(--surface2); }
    .ct td { padding: 12px 14px; color: var(--fg); vertical-align: middle; }
    .td-no { color: var(--muted); font-size: .72rem; width: 36px; text-align: center; }

    /* Avatar */
    .name-cell { display: flex; align-items: center; gap: 10px; }
    .name-av {
      width: 34px; height: 34px; border-radius: 50%; overflow: hidden; flex-shrink: 0;
      background: var(--surface2); border: 1px solid var(--border);
      display: flex; align-items: center; justify-content: center;
    }
    .name-av img { width: 100%; height: 100%; object-fit: cover; }
    .av-initials { font-family: 'Bebas Neue', sans-serif; font-size: .82rem; color: var(--lime); display: none; }

    /* Highlight de pesquisa */
    mark.hl {
      background: rgba(200,241,53,.25); color: var(--lime);
      border-radius: 2px; padding: 0 2px;
    }

    /* Category badge */
    .cat-badge {
      font-family: 'Bebas Neue', sans-serif; font-size: .68rem; letter-spacing: .1em;
      padding: 3px 8px; border-radius: 3px; text-transform: uppercase;
    }
    .cat-trabalho { background: rgba(200,241,53,.12); color: var(--lime); border: 1px solid rgba(200,241,53,.28); }
    .cat-pessoal  { background: rgba(107,107,114,.12); color: var(--muted); border: 1px solid var(--border); }

    /* Action buttons */
    .act-cell { display: flex; gap: 6px; align-items: center; justify-content: center; }
    .icon-btn {
      width: 28px; height: 28px; border-radius: 4px; border: 1px solid var(--border);
      background: transparent; display: inline-flex; align-items: center; justify-content: center;
      color: var(--muted); text-decoration: none; font-size: .82rem; transition: all .15s;
    }
    .icon-btn:hover     { border-color: var(--lime);   color: var(--lime);   background: rgba(200,241,53,.08); }
    .icon-btn.del:hover { border-color: var(--danger);  color: var(--danger);  background: rgba(255,77,77,.08); }

    /* Empty state */
    .empty-row td { padding: 60px 20px; text-align: center; color: var(--muted); }
    .empty-row .bi { font-size: 2.4rem; color: var(--border); display: block; margin-bottom: 10px; }
    .empty-suggestion { font-size: .75rem; margin-top: 6px; color: var(--muted); }
    .empty-suggestion a { color: var(--lime); text-decoration: none; }

    /* Modais */
    .modal-content { background: var(--surface); border: 1px solid var(--border); border-radius: 8px; }
    .modal-header  { background: var(--surface2); border-bottom: 1px solid var(--border); padding: 14px 20px; }
    .modal-footer  { background: var(--surface2); border-top: 1px solid var(--border); padding: 12px 20px; }
    .modal-title   { font-family: 'Bebas Neue', sans-serif; font-size: 1rem; letter-spacing: .1em; }
    .btn-close     { filter: invert(1) opacity(.4); }
    .btn-close:hover { filter: invert(1) opacity(.9); }
    .modal-body label { font-size: .65rem; letter-spacing: .12em; text-transform: uppercase; color: var(--muted); margin-bottom: 4px; display: block; }
    .modal-body .form-control,
    .modal-body .form-select {
      background: var(--surface2); border: 1px solid var(--border); color: var(--fg);
      font-family: 'Epilogue', sans-serif; font-size: .83rem; border-radius: 4px;
    }
    .modal-body .form-control:focus,
    .modal-body .form-select:focus {
      background: var(--surface2); border-color: var(--lime); color: var(--fg);
      box-shadow: 0 0 0 3px rgba(200,241,53,.12);
    }
    .modal-body .form-select option { background: #202023; }
    .modal-body .form-control::placeholder { color: var(--muted); }

    .photo-zone {
      display: flex; align-items: center; gap: 16px;
      padding: 16px 20px; border-bottom: 1px solid var(--border); background: var(--surface2);
    }
    .photo-av {
      width: 58px; height: 58px; border-radius: 50%;
      border: 2px dashed var(--border); background: var(--surface);
      display: flex; align-items: center; justify-content: center;
      overflow: hidden; cursor: pointer; flex-shrink: 0; transition: border-color .2s;
    }
    .photo-av:hover { border-color: var(--lime); }
    .photo-av img { width: 100%; height: 100%; object-fit: cover; display: none; }
    .photo-av .bi { color: var(--muted); font-size: 1.3rem; }
    .photo-info-label { font-size: .65rem; letter-spacing: .1em; text-transform: uppercase; color: var(--fg); font-weight: 600; }
    .photo-info-sub   { font-size: .7rem; color: var(--muted); line-height: 1.6; }

    .btn-modal-cancel {
      background: transparent; border: 1px solid var(--border); color: var(--muted);
      font-family: 'Epilogue', sans-serif; font-size: .8rem; padding: 7px 18px;
      border-radius: 4px; cursor: pointer; transition: all .15s;
    }
    .btn-modal-cancel:hover { border-color: var(--muted); color: var(--fg); }
    .btn-modal-save {
      background: var(--lime); color: #0f0f10; border: none;
      font-family: 'Bebas Neue', sans-serif; font-size: .85rem; letter-spacing: .1em;
      padding: 7px 22px; border-radius: 4px; cursor: pointer;
      display: inline-flex; align-items: center; gap: 5px; transition: background .15s;
    }
    .btn-modal-save:hover { background: var(--lime-dim); color: #0f0f10; }
    .btn-modal-del {
      background: var(--danger); color: #fff !important; border: none;
      font-family: 'Bebas Neue', sans-serif; font-size: .85rem; letter-spacing: .1em;
      padding: 7px 22px; border-radius: 4px; text-decoration: none;
      display: inline-flex; align-items: center; gap: 5px; transition: opacity .15s;
    }
    .btn-modal-del:hover { opacity: .82; }
    .btn-new {
      background: var(--lime); color: #0f0f10 !important;
      font-family: 'Bebas Neue', sans-serif; font-size: .82rem; letter-spacing: .1em;
      padding: 7px 20px; border-radius: 4px; border: none;
      display: inline-flex; align-items: center; gap: 6px; text-decoration: none; transition: background .15s;
    }
    .btn-new:hover { background: var(--lime-dim); }

    /* Footer */
    footer {
      background: var(--surface); border-top: 1px solid var(--border);
      padding: 12px 32px; display: flex; justify-content: space-between;
      align-items: center; flex-wrap: wrap; gap: 8px;
      font-size: .63rem; letter-spacing: .14em; color: var(--muted); text-transform: uppercase;
    }
    .footer-badge {
      background: var(--surface2); border: 1px solid var(--border);
      padding: 2px 12px; border-radius: 20px;
      font-family: 'Bebas Neue', sans-serif; font-size: .72rem; color: var(--lime);
    }

    @media (max-width: 768px) {
      .masthead, .toolbar, .results-bar, footer { padding-left: 16px; padding-right: 16px; }
      .hide-sm { display: none; }
    }
  </style>
</head>
<body>

<div class="ticker">Capacita CFTI · Formação Inicial de Desenvolvimento Backend</div>

<!-- MASTHEAD -->
<header class="masthead">
  <div class="d-flex align-items-end justify-content-between flex-wrap gap-3">
    <div>
      <div class="mast-issue">Edição Especial · Directório Pessoal</div>
      <div class="mast-title">LISTA DE <span>CONTACTOS</span></div>
    </div>
    <div class="d-flex gap-2">
      <a class="stat-card <?= $categoria===''?'active':'' ?>" href="?" style="text-decoration:none">
        <div class="stat-n"><?= $total ?></div>
        <div class="stat-l">Total</div>
      </a>
      <a class="stat-card <?= $categoria==='trabalho'?'active':'' ?>" href="?cat=trabalho<?= $query?'&q='.urlencode($query):'' ?>" style="text-decoration:none">
        <div class="stat-n" style="color:var(--lime)"><?= $trabalho ?></div>
        <div class="stat-l">Trabalho</div>
      </a>
      <a class="stat-card <?= $categoria==='pessoal'?'active-pessoal':'' ?>" href="?cat=pessoal<?= $query?'&q='.urlencode($query):'' ?>" style="text-decoration:none">
        <div class="stat-n" style="color:var(--muted)"><?= $pessoal ?></div>
        <div class="stat-l">Pessoal</div>
      </a>
    </div>
  </div>
</header>

<!-- TOOLBAR -->
<form class="toolbar" method="GET" id="searchForm">
  <?php if ($categoria): ?>
    <input type="hidden" name="cat" value="<?= htmlspecialchars($categoria) ?>">
  <?php endif; ?>

  <div class="search-wrap">
    <i class="bi bi-search"></i>
    <input
      class="search-input"
      type="text"
      name="q"
      id="searchInput"
      placeholder="Nome, apelido, email, cargo, empresa, cidade…"
      value="<?= htmlspecialchars($query) ?>"
      autocomplete="off"
    >
    <button type="button" class="btn-clear-search" id="btnClearSearch" title="Limpar pesquisa">
      <i class="bi bi-x-lg"></i>
    </button>
  </div>

  <div class="live-badge" id="liveBadge">
    <span class="live-dot"></span> Em tempo real
  </div>

  <div class="filter-chips">
    <a class="chip <?= $categoria===''?'active-all':'' ?>"
       href="<?= $query?'?q='.urlencode($query):'?' ?>">Todos</a>
    <a class="chip <?= $categoria==='trabalho'?'active-trabalho':'' ?>"
       href="?cat=trabalho<?= $query?'&q='.urlencode($query):'' ?>">Trabalho</a>
    <a class="chip <?= $categoria==='pessoal'?'active-pessoal':'' ?>"
       href="?cat=pessoal<?= $query?'&q='.urlencode($query):'' ?>">Pessoal</a>
  </div>

  <div class="flex-grow-1 d-none d-sm-block"></div>
  <a class="btn-new" href="#" data-bs-toggle="modal" data-bs-target="#modalAdd">
    <i class="bi bi-plus-lg"></i> Novo Contacto
  </a>
</form>

<!-- RESULTS BAR -->
<div class="results-bar" id="resultsBar">
  <span class="results-count" id="resultsCount">
    <?php if ($query || $categoria): ?>
      A mostrar <strong><?= count($contactos) ?></strong> de <strong><?= $total ?></strong> contactos
      <?php if ($query): ?>
        para <span class="results-query"><?= htmlspecialchars($query) ?></span>
      <?php endif; ?>
      <?php if ($categoria): ?>
        · categoria <span class="results-query"><?= htmlspecialchars($categoria) ?></span>
      <?php endif; ?>
    <?php else: ?>
      <strong><?= $total ?></strong> contactos no directório
    <?php endif; ?>
  </span>
  <?php if ($query || $categoria): ?>
    <a href="?" style="font-size:.68rem;color:var(--muted);text-decoration:none;">
      <i class="bi bi-x me-1"></i>Limpar filtros
    </a>
  <?php endif; ?>
</div>

<!-- TABLE -->
<div class="table-wrap">
  <table class="ct">
    <thead>
      <tr>
        <th>#</th>
        <th style="min-width:180px"><a href="?sort=nome<?= $query?'&q='.urlencode($query):''?>">Nome</a></th>
        <th class="hide-sm" style="min-width:130px">Apelido</th>
        <th class="hide-sm">Cargo</th>
        <th class="hide-sm">Empresa</th>
        <th class="hide-sm">Cidade</th>
        <th style="width:160px">Telefone</th>
        <th class="hide-sm" style="width:200px">E-mail</th>
        <th style="width:110px">Categoria</th>
        <th style="width:80px;text-align:center">Acções</th>
      </tr>
    </thead>
    <tbody id="contactsBody">
      <?php if (empty($contactos)): ?>
        <tr class="empty-row">
          <td colspan="10">
            <i class="bi bi-search"></i>
            Nenhum contacto encontrado<?= $query ? ' para "<strong>'.htmlspecialchars($query).'</strong>"' : '' ?>.
            <div class="empty-suggestion">
              <?php if ($query): ?>
                <a href="?">Limpar pesquisa</a> ou tente palavras diferentes.
              <?php else: ?>
                Clique em <strong>Novo Contacto</strong> para adicionar o primeiro.
              <?php endif; ?>
            </div>
          </td>
        </tr>
      <?php else: ?>
        <?php foreach ($contactos as $i => $c): ?>
        <tr data-search="<?= htmlspecialchars(mb_strtolower(
          $c['nome'].' '.$c['apelido'].' '.$c['email'].' '.$c['telefone'].' '.$c['cargo'].' '.$c['empresa'].' '.$c['cidade'].' '.$c['categoria']
        )) ?>">
          <td class="td-no"><?= $i + 1 ?></td>
          <td>
            <div class="name-cell">
              <div class="name-av">
                <img src="uploads/<?= htmlspecialchars(trim($c['foto'])) ?>" alt=""
                     onerror="this.style.display='none';this.nextElementSibling.style.display='flex'">
                <span class="av-initials">
                  <?= strtoupper(mb_substr($c['nome'],0,1).mb_substr($c['apelido'],0,1)) ?>
                </span>
              </div>
              <span class="name-text" data-field="nome"><?= htmlspecialchars($c['nome']) ?></span>
            </div>
          </td>
          <td class="hide-sm" data-field="apelido"><?= htmlspecialchars($c['apelido']) ?></td>
          <td class="hide-sm" data-field="cargo"><?= htmlspecialchars($c['cargo']) ?></td>
          <td class="hide-sm" data-field="empresa"><?= htmlspecialchars($c['empresa']) ?></td>
          <td class="hide-sm" data-field="cidade"><?= htmlspecialchars($c['cidade']) ?></td>
          <td data-field="telefone"><?= htmlspecialchars($c['telefone']) ?></td>
          <td class="hide-sm" data-field="email"><?= htmlspecialchars($c['email']) ?></td>
          <td>
            <span class="cat-badge cat-<?= htmlspecialchars(trim($c['categoria'])) ?>">
              <?= htmlspecialchars($c['categoria']) ?>
            </span>
          </td>
          <td>
            <div class="act-cell">
              <a class="icon-btn" href="editar-contacto.php?id=<?= $c['id'] ?>" title="Editar">
                <i class="bi bi-pencil"></i>
              </a>
              <a class="icon-btn del" href="#" title="Eliminar"
                 data-bs-toggle="modal" data-bs-target="#modalDelete"
                 data-id="<?= $c['id'] ?>"
                 data-nome="<?= htmlspecialchars($c['nome'].' '.$c['apelido']) ?>">
                <i class="bi bi-trash3"></i>
              </a>
            </div>
          </td>
        </tr>
        <?php endforeach; ?>
      <?php endif; ?>
    </tbody>
  </table>
</div>

<!-- FOOTER -->
<footer>
  <span>Lista de Contactos · Directório Pessoal</span>
  <span class="footer-badge" id="footerCount"><?= count($contactos) ?> Registos</span>
  <span><?= date('d \d\e F \d\e Y') ?></span>
</footer>


<!-- MODAL: NOVO CONTACTO -->
<div class="modal fade" id="modalAdd" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable" style="max-width:560px">
    <div class="modal-content">
      <div class="modal-header">
        <span class="modal-title">Novo Contacto</span>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <form action="adiciona-contacto.php" method="POST" enctype="multipart/form-data">
        <div class="photo-zone">
          <div class="photo-av" id="addPhotoAv" onclick="document.getElementById('addFotoInput').click()">
            <img id="addPhotoPreview" alt="">
            <i class="bi bi-person" id="addPhotoIcon"></i>
          </div>
          <div>
            <div class="photo-info-label">Foto de Perfil</div>
            <div class="photo-info-sub">Clique no círculo para seleccionar<br>JPG, PNG · Máx. 5 MB</div>
            <input type="file" name="foto" id="addFotoInput" accept="image/*" class="d-none">
          </div>
        </div>
        <div class="modal-body">
          <div class="row g-3">
            <div class="col-sm-6"><label>Primeiro Nome *</label><input type="text" name="primeiro_nome" class="form-control" placeholder="Ana" required></div>
            <div class="col-sm-6"><label>Apelido *</label><input type="text" name="apelido" class="form-control" placeholder="Silva" required></div>
            <div class="col-12"><label>Email</label><input type="email" name="email" class="form-control" placeholder="ana@exemplo.com"></div>
            <div class="col-sm-6"><label>Telefone</label><input type="tel" name="telefone" class="form-control" placeholder="+244 900 000 000"></div>
            <div class="col-sm-6"><label>Categoria</label>
              <select name="categoria" class="form-select">
                <option value="trabalho">Trabalho</option>
                <option value="pessoal">Pessoal</option>
              </select>
            </div>
            <div class="col-sm-6"><label>Cargo</label><input type="text" name="cargo" class="form-control" placeholder="Designer"></div>
            <div class="col-sm-6"><label>Empresa</label><input type="text" name="empresa" class="form-control" placeholder="Empresa Lda."></div>
            <div class="col-12"><label>Cidade</label><input type="text" name="cidade" class="form-control" placeholder="Luanda"></div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn-modal-cancel" data-bs-dismiss="modal">Cancelar</button>
          <button type="submit" class="btn-modal-save"><i class="bi bi-floppy"></i> Guardar</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- MODAL: ELIMINAR -->
<div class="modal fade" id="modalDelete" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" style="max-width:400px">
    <div class="modal-content">
      <div class="modal-header">
        <span class="modal-title">Confirmar Eliminação</span>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body text-center py-4">
        <i class="bi bi-exclamation-triangle-fill" style="font-size:2.6rem;color:var(--danger)"></i>
        <p class="mt-3 mb-1" style="font-size:.88rem">
          Tem a certeza que deseja eliminar<br>
          <strong id="deleteNomeLabel" style="color:var(--fg)"></strong>?
        </p>
        <p class="mb-0" style="font-size:.73rem;color:var(--muted)">Esta acção não pode ser desfeita.</p>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn-modal-cancel" data-bs-dismiss="modal">Cancelar</button>
        <a id="deleteConfirmLink" href="#" class="btn-modal-del">
          <i class="bi bi-trash3"></i> Eliminar
        </a>
      </div>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
// ── Normalização (espelha o PHP) ───────────────────────────────────────────────
function normalize(str) {
  return str
    .toLowerCase()
    .normalize('NFD')
    .replace(/[\u0300-\u036f]/g, ''); // remove diacríticos
}

// ── Live Search ────────────────────────────────────────────────────────────────
const searchInput   = document.getElementById('searchInput');
const btnClear      = document.getElementById('btnClearSearch');
const liveBadge     = document.getElementById('liveBadge');
const resultsCount  = document.getElementById('resultsCount');
const footerCount   = document.getElementById('footerCount');
const rows          = document.querySelectorAll('#contactsBody tr[data-search]');
const totalRecords  = <?= $total ?>;

let debounceTimer = null;

// Mostrar/esconder botão de limpar
function updateClearBtn() {
  btnClear.style.display = searchInput.value.length > 0 ? 'block' : 'none';
}

// Highlight de texto num elemento
function highlightText(el, tokens) {
  const original = el.dataset.original ?? el.textContent;
  el.dataset.original = original;

  if (!tokens.length) {
    el.innerHTML = original;
    return;
  }

  let html = original;
  tokens.forEach(token => {
    if (!token) return;
    const escaped = token.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
    const regex = new RegExp(`(${escaped})`, 'gi');
    html = html.replace(regex, '<mark class="hl">$1</mark>');
  });
  el.innerHTML = html;
}

function doLiveFilter() {
  const raw    = searchInput.value;
  const tokens = raw.trim().split(/\s+/).filter(Boolean).map(normalize);
  let visible  = 0;

  rows.forEach((row, idx) => {
    const haystack = normalize(row.dataset.search);
    const match    = tokens.every(t => haystack.includes(t));

    row.style.display = match ? '' : 'none';

    if (match) {
      visible++;
      // Renumerar
      row.querySelector('.td-no').textContent = visible;

      // Highlight nos campos visíveis
      row.querySelectorAll('[data-field]').forEach(cell => {
        // Guardar texto puro (sem HTML de highlight anterior)
        if (!cell.dataset.original) cell.dataset.original = cell.textContent;
        highlightText(cell, raw.trim() ? tokens.map(t => {
          // Encontrar a versão original do token (sem normalizar) para highlight visual
          return t;
        }) : []);
      });
      // Highlight no nome dentro da name-cell
      const nameText = row.querySelector('.name-text');
      if (nameText) {
        if (!nameText.dataset.original) nameText.dataset.original = nameText.textContent;
        highlightText(nameText, tokens);
      }
    }
  });

  // Atualizar contador
  if (raw.trim()) {
    liveBadge.style.display  = 'inline-flex';
    resultsCount.innerHTML   =
      `A mostrar <strong>${visible}</strong> de <strong>${totalRecords}</strong> contactos` +
      ` para <span class="results-query">${escapeHtml(raw.trim())}</span>`;
  } else {
    liveBadge.style.display  = 'none';
    resultsCount.innerHTML   = `<strong>${totalRecords}</strong> contactos no directório`;
  }

  footerCount.textContent = visible + ' Registos';
  updateClearBtn();
}

function escapeHtml(str) {
  return str.replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;');
}

// Debounce de 180ms para não filtrar a cada tecla
searchInput.addEventListener('input', () => {
  clearTimeout(debounceTimer);
  debounceTimer = setTimeout(doLiveFilter, 180);
});

// Limpar pesquisa
btnClear.addEventListener('click', () => {
  searchInput.value = '';
  doLiveFilter();
  searchInput.focus();
});

// Impedir submit do form no Enter (usa live filter em vez disso)
// mas permite submit se o utilizador quer pesquisa server-side
searchInput.addEventListener('keydown', e => {
  if (e.key === 'Enter') {
    // Deixa o form submeter normalmente para server-side
    return;
  }
});

// Estado inicial
updateClearBtn();
if (searchInput.value) doLiveFilter();


// ── Preview foto (modal Adicionar) ─────────────────────────────────────────────
document.getElementById('addFotoInput').addEventListener('change', function () {
  const file = this.files[0];
  if (!file) return;
  const reader = new FileReader();
  reader.onload = e => {
    const img  = document.getElementById('addPhotoPreview');
    const icon = document.getElementById('addPhotoIcon');
    img.src = e.target.result;
    img.style.display = 'block';
    icon.style.display = 'none';
  };
  reader.readAsDataURL(file);
});

// ── Modal Eliminar — dinâmico ──────────────────────────────────────────────────
document.getElementById('modalDelete').addEventListener('show.bs.modal', e => {
  const btn = e.relatedTarget;
  document.getElementById('deleteNomeLabel').textContent = btn.dataset.nome;
  document.getElementById('deleteConfirmLink').href = 'eliminar-contacto.php?id=' + btn.dataset.id;
});
</script>
</body>
</html>
