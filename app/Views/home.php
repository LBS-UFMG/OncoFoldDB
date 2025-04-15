<!-- modelo para criação de views: copie este arquivo e apague os comentários -->
<?= $this->extend('template') ?>

<?= $this->section('scripts') ?>
<!-- adicione links para scripts aqui -->
<?= $this->endSection() ?>

<?= $this->section('conteudo') ?>

<div class="container col-xxl-10 px-2 py-0">
  <div class="row flex-lg-row-reverse align-items-center g-5 py-5">
    
    <!-- Image Section -->
    <div class="col-10 col-sm-8 col-md-6">
      <img 
        src="<?= base_url('/img/home.png') ?>" 
        class="d-block mx-lg-auto img-fluid" 
        alt="3D protein structure visual from CosmicLBS" 
        width="450" 
        loading="lazy"
      >
    </div>
    
    <!-- Text Section -->
    <div class="col-md-6 text-center text-md-start">
      <h1 class="display-5 fw-semibold text-body-emphasis lh-1 mb-3">OncoFoldDB</h1>

      <p class="lead">
        OncoFoldDB is a curated database of 3D structures for cancer-related driver and non-driver mutations, modeled with AlphaFold. 
        It enables comparative analyses of pathogenic and neutral variants, supporting research in oncology, structural modeling,
        and drug discovery.
      </p>

      <div class="d-grid gap-2 d-md-flex justify-content-md-start mt-3">
        <a 
          href="<?= base_url('explore') ?>" 
          class="btn btn-primary btn-lg px-4 me-md-2 azul" 
          role="button" 
          aria-label="Explore the CosmicLBS database"
        >
          Explore
        </a>
        <a 
          href="#examples" 
          class="btn btn-outline-dark btn-lg px-4" 
          role="button" 
          aria-label="View structural examples in CosmicLBS"
        >
          Examples
        </a>
      </div>
    </div>

  </div>
</div>


<div class="bg-light my-5 py-5">
  <div id="info" class="container">
    <div class="row">
      <div class="col-xs-12 col-md-3">
        <div class="card p-2" style="border-left: #031430 5px solid; color: #ccc">
          <div class="caption">
            <div class="row">
              <div class="col-md-3 text-dark" style="font-size: 72px">
                <i class="bi bi-braces-asterisk"></i>
              </div>
              <div class="col-md-9 text-end">
                <h3 class="mt-4">
                  <strong class="texto-azul">
                    <?=$h1?>
                  </strong>
                </h3>
                <p class="text-muted small"><strong>Genes</strong></p>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div class="col-xs-12 col-md-3">
        <div class="card p-2" style="border-left: #031430 5px solid; color: #ccc">
          <div class="caption">
            <div class="row">
              <div class="col-md-3 text-dark" style="font-size: 72px">
                <i class="bi bi-info-circle-fill"></i>
              </div>
              <div class="col-md-9 text-end">
                <h3 class="mt-4">
                  <strong class="texto-azul">
                  <?=$h2?>
                  </strong>
                </h3>
                <p class="text-muted small"><strong>Drivers mutations</strong></p>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div class="col-xs-12 col-md-3">
        <div class="card p-2" style="border-left: #031430 5px solid; color: #ccc">
          <div class="caption">
            <div class="row">
              <div class="col-md-3 text-dark" style="font-size: 72px">
                <i class="bi bi-exclude"></i>
              </div>
              <div class="col-md-9 text-end">
                <h3 class="mt-4"><strong class="texto-azul">
                  <?=$h3?>
                  </strong>
                </h3>
                <p class="text-muted small"><strong>Non-drivers mutations</strong></p>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div class="col-xs-12 col-md-3">
        <div class="card p-2" style="border-left: #031430 5px solid; color: #ccc">
          <div class="caption">
            <div class="row">
              <div class="col-md-3 text-dark" style="font-size: 72px">
                <i class="bi bi-hurricane"></i>
              </div>
              <div class="col-md-9 text-end">
                <h3 class="mt-4">
                  <strong class="texto-azul">
                    <?=$h4?> 
                  </strong>
                </h3>
                <p class="text-muted small"><strong>3D STRUCTURES</strong></p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
    <h5 class="text-muted small">*Last updated on: <?=$update?></h5>
  </div>
</div>

<div class="container mt-5" id="run">
  <div class="row">
    <div class="col-xs-12 col-lg-12">
      <div class="alert alert-light shadow my-4 " style="border-left: #4080de 5px solid">
        <div class="caption">
          <div class="row">
            <div class="col-md-12 p-4">
              <h4 class="" style="color:#4080de"><strong>How to cite:</strong></h4>
              <p class="small" id="browse">In development
              </p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- ************************ EXAMPLES ************************ -->
<!-- 
<div class="container mt-5 my-5 px-5 pb-5" id="examples">
  <h1 class="pt-5"><strong>Examples</strong></h1>
  <hr>
  <p class="text-muted">Click on one of the following PDB-IDs to explore the corresponding entry:</p>
  <div class="row">
    <div class="col">
      <label class="badge bg-light text-dark">Protein single-chain</label>
      <a href="<?= base_url('/entry/1K0P') ?>" class="badge bg-primary azul">1K0P</a>
      <a href="<?= base_url('/entry/1TPM') ?>" class="badge bg-primary azul">1TPM</a>
      <a href="<?= base_url('/entry/2LZM') ?>" class="badge bg-primary azul">2LZM</a>
      <a href="<?= base_url('/entry/4MDP') ?>" class="badge bg-primary azul">4MDP</a>
    </div>
  </div>

  <div class="row">
    <div class="col">
      <label class="badge bg-light text-dark">Protein multi-chain complex</label>
      <a href="<?= base_url('/entry/1SHR') ?>" class="badge bg-primary azul">1SHR</a>
    </div>
  </div>

  <div class="row">
    <div class="col">
      <label class="badge bg-light text-dark">Protein-peptide</label>
      <a href="<?= base_url('/entry/1A1M') ?>" class="badge bg-primary azul">1A1M</a>
    </div>
  </div>

  <div class="row">
    <div class="col">
      <label class="badge bg-light text-dark">Protein-DNA</label>
      <a href="<?= base_url('/entry/3L1P') ?>" class="badge bg-primary azul">3L1P</a>
    </div>
  </div>

  <div class="row">
    <div class="col">
      <label class="badge bg-light text-dark">Protein-RNA</label>
      <a href="<?= base_url('/entry/4PMI') ?>" class="badge bg-primary azul">4PMI</a>
    </div>
  </div>
</div> -->

<script>
  const go = document.getElementById('go');

  go.addEventListener('click', () => {
    let url = document.getElementById('pdb_go').value;
    url = url.toUpperCase();
    if (url) {
      if (url.length != 4) {
        window.location.href = '<?= base_url("/entry/404") ?>';
      }
      window.location.href = '<?= base_url() ?>entry/' + url;
    }
  });

  function redirectToURL2(event) {
    // Verificar se a tecla pressionada foi Enter (código 13)
    if (event.keyCode === 13) {
      event.preventDefault(); // Prevenir o envio do formulário
      let url = document.getElementById('pdb_go').value;
      url = url.toUpperCase();
      if (url) {
        if (url.length != 4) {
          window.location.href = '<?= base_url("/entry/404") ?>';
        }
        window.location.href = '<?= base_url() ?>entry/' + url;
      }
    }
  }
</script>

<?= $this->endSection() ?>