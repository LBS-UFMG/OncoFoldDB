<?= $this->extend('template') ?>
<?= $this->section('conteudo') ?>
<!-- Conteúdo personalizado -->

<div id="loading">
    <div class="text-center">
        <img src="<?=base_url('/img/cocadito-loading.png')?>" width="200px"><br>
        <div class="spinner-border spinner-border-sm" role="status"></div>
        <strong class="ms-2">Loading...</strong>
    </div>
</div>

<div class="container-fluid py-4 px-5">

    <h1 class="pb-5 text-dark">Genes</h1>

    <div id="explore">
        <div class="container-fluid">
            <table id="table_explore" class="table table-striped table-hover" style="width:100%;">
                <thead>
                    <tr class="tableheader">
                        <th class="dt-center">Gene ID</th>
                        <th class="dt-center">Cancer role</th>
                        <th class="text-center">PMID</th>
                        <th class="dt-center">Driver Mutations (COSMIC)</th>
                        <th class="dt-center">Passenger Mutations (COSMIC)</th>
                    </tr>
                </thead>
                <tbody>
                </tbody>
            </table>
        </div>
    </div>

</div>
<!-- / FIM Conteúdo personalizado -->
<?= $this->endSection() ?>


<?= $this->section('scripts') ?>
<script>
    $(() => {

        const lerDados = (arquivo) => {

            // ler arquivo usando jQuery
            $.ajax({
                url: arquivo,
                success: (dados) => {
                    dados_formatados = formatarTabela(dados)

                    plotar(dados_formatados)
                }
            });
        }

        // formatar tabela --> INÍCIO 
        const formatarTabela = (dados) => {

            let dados_tabelados = [];

            // separa as linhas
            let linhas = dados.split("\n")

            // para cada linha
            for (let linha of linhas) {

                // remove caracteres especiais 
                linha = linha.replace("\r", "")

                // separa as células
                let celulas = linha.split("\t")
                celulas[0] = `<a class="text-primary text-decoration-none fw-semibold" href="<?=base_url()?>entry/${celulas[0]}">${celulas[0]}</a>`;
                
                console.log(celulas)

                // extrai PMID
                const pubmedField = celulas[3];
                let texto = "";
                let link = "";

                if (pubmedField && pubmedField.includes("/?term=")) {
                    const pmidList = pubmedField.split("?term=")[1];
                    texto = pmidList || "";
                    link = pubmedField;
                } else if (pubmedField) {
                    const pmidMatch = pubmedField.match(/pubmed\.ncbi\.nlm\.nih\.gov\/(\d+)/);
                    const pmid = pmidMatch ? pmidMatch[1] : "";
                    texto = pmid;
                    link = pmid ? `https://pubmed.ncbi.nlm.nih.gov/${pmid}/` : "";
                }

                const query = texto ? texto.split(',').join(',<wbr>') : "";

                celulas[3] = texto
                    ? `<div class="text-start">
                        <a href="${link}" target="_blank" 
                            class="text-primary text-decoration-none fw-normal">
                            ${query}
                        </a>
                    </div>`
                    : "";

                dados_tabelados.push([
                    celulas[0],
                    celulas[2],
                    celulas[3],
                    celulas[4],
                    celulas[5]
                ]);
            }

            return dados_tabelados
        }
        // formatar tabela --> FIM 


        // plotando a tabela
        const plotar = (dados) => {

            console.log(dados)

            // ativar datatable
            $("#table_explore").DataTable({
                "data": dados,
                autoWidth: false,
                columnDefs: [
                    { width: '10%', targets: 0 },   
                    { width: '15%', targets: 1 },   
                    { width: '15%', targets: 2 },   
                    { width: '30%', targets: 3 }, 
                    { width: '30%', targets: 4 }  
                ]
                // "order": [
                //     [0, 'asc']
                // ] // ordena pela coluna 0
            })
        }

        lerDados("<?= base_url('data/list.csv') ?>");

    })

    
</script>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>

<script>
        $(()=>setTimeout(() => $('#loading').fadeOut(), 1000));

// tooltips
    const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]');
    const tooltipList = [...tooltipTriggerList].map(tooltipTriggerEl => new bootstrap.Tooltip(tooltipTriggerEl));
</script>
<?= $this->endSection() ?>
