<?= $this->extend('template') ?>
<?= $this->section('conteudo') ?>

<!-- Custom content -->
<div id="loading">
    <div class="text-center">
        <img src="<?= base_url('/img/cocadito-loading.png') ?>" width="200px"><br>
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
                        <th class="text-center">PubMed</th>
                        <th class="dt-center">Driver Mutations (COSMIC)</th>
                        <th class="dt-center">Passenger Mutations (COSMIC)</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>
</div>

<?= $this->endSection() ?>


<?= $this->section('scripts') ?>
<script>
    $(() => {

        // Load data file via jQuery
        const loadData = (file) => {
            $.ajax({
                url: file,
                success: (content) => {
                    const formattedData = formatTable(content);
                    renderTable(formattedData);
                }
            });
        }

        // Format table content
        const formatTable = (rawData) => {
            const tableData = [];
            const lines = rawData.split("\n");

            for (let line of lines) {
                line = line.replace("\r", ""); // clean carriage return

                const cells = line.split("\t");

                // Gene ID
                cells[0] = `<a class="text-primary text-decoration-none fw-semibold" href="<?= base_url() ?>entry/${cells[0]}">${cells[0]}</a>`;

                // Extract PubMed link from column 4
                const pubmedField = cells[3];
                let displayText = "", link = "";

                if (pubmedField && pubmedField.includes("/?term=")) {
                    const pmidList = pubmedField.split("?term=")[1];
                    displayText = pmidList || "";
                    link = pubmedField;
                } else if (pubmedField) {
                    const match = pubmedField.match(/pubmed\.ncbi\.nlm\.nih\.gov\/(\d+)/);
                    const pmid = match ? match[1] : "";
                    displayText = pmid;
                    link = pmid ? `https://pubmed.ncbi.nlm.nih.gov/${pmid}/` : "";
                }

                cells[3] = displayText
                    ? `<div class="text-center">
                        <a href="${link}" target="_blank" 
                           class="text-primary text-decoration-none fw-normal"
                           title="View on PubMed">
                            <i class="bi bi-journal-text"></i>
                        </a>
                    </div>`
                    : "";

                tableData.push([
                    cells[0],
                    cells[2],
                    cells[3],
                    cells[4],
                    cells[5]
                ]);
            }

            return tableData;
        }

        // Render DataTable
        const renderTable = (data) => {
            $("#table_explore").DataTable({
                data: data,
                autoWidth: false,
                columnDefs: [
                    { width: '10%', targets: 0 },
                    { width: '15%', targets: 1 },
                    { width: '15%', targets: 2 },
                    { width: '30%', targets: 3 },
                    { width: '30%', targets: 4 }
                ]
                // Uncomment to enable sorting by first column
                // order: [[0, 'asc']]
            });
        }

        // Load CSV file with gene mutation data
        loadData("<?= base_url('data/genes_table.csv') ?>");
    });

    // Hide loading screen after delay
    $(() => setTimeout(() => $('#loading').fadeOut(), 1000));
</script>
<?= $this->endSection() ?>


<?= $this->section('scripts') ?>
<script>
    // Bootstrap tooltips activation
    const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]');
    const tooltipList = [...tooltipTriggerList].map(el => new bootstrap.Tooltip(el));
</script>
<?= $this->endSection() ?>