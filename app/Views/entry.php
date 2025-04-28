<?= $this->extend('template') ?>
<?= $this->section('conteudo') ?>
<!-- Conteúdo personalizado -->

<link rel="stylesheet" href="<?php echo base_url('/css/dt.css'); ?>">
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>

<div id="loading">
    <div class="text-center">
        <img src="<?= base_url('/img/cocadito-loading.png') ?>" width="200px"><br>
        <div class="spinner-border spinner-border-sm" role="status"></div>
        <strong class="ms-2">Loading...</strong>
    </div>
</div>
<div style="background-color:#e4e4e4; height:180px; margin: -25px -10px 20px -10px;">
    <div class="container-fluid px-5">
        <div class="row">
            <div class="col-md-9 col-xs-12 pt-2">
                <h2 class="title_h2 pt-4">
                    <strong><?php echo $gene_id; ?></strong>
                    <!-- div class="dropdown d-inline ms-2" title="Export files">
                        <div class="dropdown d-inline">
                            <button class="btn btn-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                Download
                            </button>
                            <ul class="dropdown-menu">
                                <li><b class="ms-3">Unavailable<br></b></li>
                                <li><a class="dropdown-item mt-2" href="<?php echo base_url(); ?>data/pdb/<?= substr($id, 0, 1) ?>/<?= $id ?>/<?= $id ?>_contacts.csv">Contacts</a></li>
                                <li><a class="dropdown-item" href="https://files.rcsb.org/download/<?php echo $id; ?>.cif">PDB file</a></li>
                            </ul>
                        </div>
                    </div>
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#contactMap">
                        Show contact map <i class="bi bi-image"></i>
                    </button> -->
                </h2>
                <h4>
                    <?php echo $gene_name; ?>
                </h4>
                <div class="col">
                    <p class="mb-0 mt-3"><strong>Driver Mutations Count: </strong><?= count(array_filter($drivers, fn($v) => trim($v) === '')) > 0 ? 0 : count($drivers); ?> <span class="px-4">|</span> <strong>Passenger Mutations Count: </strong><?= count(array_filter($nondrivers, fn($v) => trim($v) === '')) > 0 ? 0 : count($nondrivers); ?> </p>
                </div>
            </div>

        </div>
    </div>
</div>


<div class="container-fluid px-5">
    <div class="row">
        <div class="col-md-6" ng-if="cttlok">

            <!-- <div class="btn-group btn-group-sm" role="group" aria-label="...">
                <span class="btn btn-outline-dark" id="basic-addon1"><b>Filters: </b></span>
                <button type="button" id="show_all" class="btn btn-dark">Show all</button>             
                <button type="button" id="hb" class="btn btn-success">Hydrogen bonds</button>          
                <button type="button" id="at" class="btn btn-info">Attractive</button>       
                <button type="button" id="re" class="btn btn-danger">Repulsive</button>          
                <button type="button" id="hy" class="btn btn-warning">Hydrophobic</button>              
                <button type="button" id="ar" class="btn btn-secondary">Aromatic</button>          
                <button type="button" id="sb" class="btn btn-primary">Salt Bridge</button>           
                <button type="button" id="db" class="btn btn-light border">Disulfide</button>
            </div> -->
            
            <!-- <span class="small text-muted"><input type="checkbox" id="side_chain" class="btn btn-light border ms-1"> Only side chain contacts</span> -->

            <br>

            <div class="table-responsive">
                <table class="display" id="mut">
                    <thead>
                        <tr>
                            <th class="dt-center">Mutation (HGVS)</th>
                            <th class="dt-center">Variant Classification</th>
                            <th class="dt-center">PDB Download</th>
                            <th class="dt-center">Reference</th><!-- NOVA COLUNA -->
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                            $mutations = [];
                            foreach ($drivers as $d)      $mutations[] = ['mutation'=>$d,'type'=>'Driver'];
                            foreach ($nondrivers as $d)   $mutations[] = ['mutation'=>$d,'type'=>'Passenger'];

                            foreach ($mutations as $m):
                                if (trim($m['mutation'])==='') continue;
                                $mutation = htmlspecialchars($m['mutation']);
                                $type     = $m['type'];
                                $color    = $type==='Driver' ? 'bg-danger' : 'bg-primary';
                                $pdb_path = $type==='Driver' ? 'drivers' : 'non-drivers';
                        ?>
                        <tr onclick="selectID(glviewer, this.children[0].innerHTML, 1, this.children[1].innerHTML, this.children[3]?.innerHTML ?? '', this.children[6]?.innerHTML ?? '')"
                            id="<?= $mutation ?>">
                            <td class="fw-semibold"><?= "p.$mutation" ?></td>
                            <td class="<?= $color ?> text-white"><?= $type ?></td>
                            <td class="text-center">
                                <a href="<?= base_url("data/models/$pdb_path/$id/p$mutation/ranked_0.pdb") ?>">
                                    <i class="bi bi-download"></i>
                                </a>
                            </td>
                            <td class="text-center" data-ref><!-- preenchido via JS --></td><!-- NOVA CELULA -->
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>


        <div class="col-md-6">

            <style>
                .affix {
                    top: 100px;
                    z-index: 9999 !important;
                }
            </style>

            <style>
                #pdb canvas {
                    position: relative !important;
                }
            </style>
            <div data-spy="affix" id="affix" data-offset-top="240" data-offset-bottom="250">
                <div id="pdb" style="min-height: 400px; height: 50vh; min-width:280px; width: 100%"></div>
                <p style="color:#ccc; text-align: right">Wild protein</p>
            </div>
        </div>
    </div>
</div>


<!-- Modal -->
<div class="modal fade" id="contactMap" tabindex="-1" aria-labelledby="contactMap" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-3 text-center w-100" id="contactMapTitle"><strong>Contacts map for <?= $id ?></strong></h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">

            <div id="controls">
                    <div class="row px-4">
                        <div class="col">
                            <label for="chainX">X-axis Chain:</label>
                            <select id="chainX" class="form-select" onchange="updateChart()"></select>
                        </div>
                        <div class="col">
                            <label for="chainY">Y-axis Chain:</label>
                            <select id="chainY" class="form-select" onchange="updateChart()"></select>
                        </div>
                        <!-- <div class="col">
                                <button class="btn btn-primary w-100 mt-4" onclick="updateChart()">Update chart</button>
                            </div> -->
                        <div class="col">
                            <button id="saveButton" class="btn btn-success w-100 mt-4" onclick="saveChart()">Save figure</button>
                        </div>
                    </div>
                </div>

                <style>
                    canvas {
                        max-width: calc(100vh - 150px) !important;
                    }
                </style>
                <div class="row">

                    <div class="col">
                        <canvas id="scatterChart" class="p-4"></canvas>
                        <div id="legend" class="pb-3"></div>
                    </div>
                </div>
            </div>
            
            <div class="modal-footer bg-white">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Return to Top -->
<a href="#" title="Return to top" style="position:fixed; right:10px; bottom:10px; color:#cccccc77"><span class="glyphicon glyphicon-chevron-up small" aria-hidden="true">Return to top</span></a>

<script>
    // loading
    $(() => setTimeout(() => $('#loading').fadeOut(), 1000));

    $(() => {
        $.get("<?= base_url('data/mutation_table.csv') ?>")
        .done(raw => {
            const Gene = s => s.trim().toUpperCase();
            const Mut  = s => s.trim().replace(/^p\./i, '').toUpperCase();
            const map = {};
            raw.trim().split('\n').forEach(l => {
                const [gene, mut, url] = l.split(/\s+/);
                if (!gene || !mut || !url) return;
                const gene_key = Gene(gene);
                const mut_key  = Mut(mut);
                if (!map[gene_key]) map[gene_key] = {};
                map[gene_key][mut_key] = url;
            });

            const gene = Gene('<?= $gene_id ?>');

            // DataTable Contendo as Mutações
            function drawTable() {
                $('#mut tbody tr').each(function () {
                    const $cells = $(this).children('td');
                    const mut = Mut($cells.eq(0).text());
                    const url = map[gene]?.[mut];
                    $cells.eq(3).html(
                        url ? `<a target="_blank" href="${url}"><i class="bi bi-journal-text"></i></a>` : '–'
                    );
                });
            }

            // Inicializa DataTable
            if (!$.fn.DataTable.isDataTable('#mut')) {
                $('#mut').DataTable({
                    order: [],
                    paging: true
                });
            }

            drawTable();

            $('#mut').on('draw.dt', function () {
                drawTable();
            });

        })
        .fail(err => console.error('Error reading mutation_table.csv.', err));
    });

    $(document).ready(function() {
        var table = $('#mut').DataTable({
            order: [],
            "paging": true
        });
        
        $('#side_chain').click(function() {
            if ($("#side_chain").prop("checked")) {
                table
                    .columns(3).search("CB|CG|CG1|CG2|CD|CD1|CD2|CE|CE1|CE2|CE3|CZ|CZ2|CZ3|CH2|ND1|ND2|NE|NE1|NE2|NZ|OD1|OD2|OE1|OE2|OG|OG1|OH|SD|SG", true, false)
                    .columns(6).search("CB|CG|CG1|CG2|CD|CD1|CD2|CE|CE1|CE2|CE3|CZ|CZ2|CZ3|CH2|ND1|ND2|NE|NE1|NE2|NZ|OD1|OD2|OE1|OE2|OG|OG1|OH|SD|SG", true, false)
                    .draw();
            } else {
                table.columns(3).search(".*", true, false)
                    .columns(6).search(".*", true, false).draw();
            }
        });

        $('#at').click(function() {
            table.columns(9).search("AT", true, false).draw();
        });
        $('#hb').click(function() {
            table.columns(9).search("HB", true, false).draw();
        });
        $('#re').click(function() {
            table.columns(9).search("RE", true, false).draw();
        });
        $('#ar').click(function() {
            table.columns(9).search("AS|SPA|SPE|SOT", true, false).draw();
        });
        $('#hy').click(function() {
            table.columns(9).search("HY", true, false).draw();
        });
        $('#sb').click(function() {
            table.columns(9).search("SB", true, false).draw();
        });
        $('#db').click(function() {
            table.columns(9).search("DS", true, false).draw();
        });
        $('#show_all').click(function() {
            table.columns(9).search(".*", true, false).draw();
        });
    });


    $('nav').css('position', 'relative');

    function highlight(pos) {
        $(pos).css("background-color", "#f2dede");
    }

    // 3DMOL **********************************************************************
    /* Select ID */
    function selectID(glviewer, residues, type, chain, a1, a2) {

        residues = residues.split("/");

        var res1 = residues[0].substr(1);
        var res2 = residues[1].substr(1);

        glviewer.setStyle({}, {
            line: {
                color: 'grey'
            },
            cartoon: {
                color: 'white'
            }
        }); /* Cartoon multi-color */
        glviewer.setStyle({
            resi: res1
        }, {
            stick: {
                colorscheme: 'whiteCarbon'
            }
        });
        glviewer.setStyle({
            resi: res2
        }, {
            stick: {
                colorscheme: 'whiteCarbon'
            }
        });

        glviewer.zoomTo({
            resi: [res1, res2],
            chain: chain
        });

        // linha tracejada
        let atm1 = glviewer.selectedAtoms({ resi: res1, atom: a1 }); // Resíduo 10, átomo O
        let atm2 = glviewer.selectedAtoms({ resi: res2, atom: a2 }); // Resíduo 20, átomo N

        // Garantir que os átomos foram encontrados antes de desenhar a linha
        if (atm1.length > 0 && atm2.length > 0) {
            var atom1 = atm1[0]; // Primeiro átomo correspondente
            var atom2 = atm2[0]; // Primeiro átomo correspondente

            console.log(atom2,'aqui')

            // Adicionar a linha tracejada entre os átomos
            glviewer.addLine({
                dashed: true,
                start: { x: atom1.x, y: atom1.y, z: atom1.z },
                end: { x: atom2.x, y: atom2.y, z: atom2.z },
                color: "red",
                dashLength: 0.2, // Comprimento dos traços
                linewidth:5, // Define a grossura da linha
                gapLength:0.1
            });
        }
        // fim linha tracejada

        glviewer.render();

    }


    function selectPDB(id) {

        var ids = id.split("_");
        var mut = ids[1].replace("/", "_");

        try {
            var pos = mut.split("_");
            var pos1 = pos[0].substr(1, pos[0].length - 2);
            var pos2 = pos[1].substr(1, pos[1].length - 2);
            var pos1a = Number(pos1) - 1;
            var pos1d = Number(pos1) + 1;
            var pos2a = Number(pos2) - 1;
            var pos2d = Number(pos2) + 1;
            pos1a = pos1a.toString();
            pos1d = pos1d.toString();
            pos2a = pos2a.toString();
            pos2d = pos2d.toString();
        } catch (err) {
            var erro = 1;
        }


        var atomcallback = function(atom, viewer) {
            if (atom.clickLabel === undefined ||
                !atom.clickLabel instanceof $3Dmol.Label) {
                atom.clickLabel = viewer.addLabel(atom.resn + " " + atom.resi + " (" + atom.elem + ")", {
                    fontSize: 10,
                    position: {
                        x: atom.x,
                        y: atom.y,
                        z: atom.z
                    },
                    backgroundColor: "black"
                });
                atom.clicked = true;
            }

            //toggle label style
            else {

                if (atom.clicked) {
                    var newstyle = atom.clickLabel.getStyle();
                    newstyle.backgroundColor = 0x66ccff;

                    viewer.setLabelStyle(atom.clickLabel, newstyle);
                    atom.clicked = !atom.clicked;
                } else {
                    viewer.removeLabel(atom.clickLabel);
                    delete atom.clickLabel;
                    atom.clicked = false;
                }
            }
        };

    }

    $(document).ready(function () {

        <?php if(count($drivers) > 0): ?>
        var txt = "<?php echo base_url(); ?>data/models/drivers/<?php echo $id; ?>/p<?php echo $drivers[0]; ?>/ranked_0.pdb";
        <?php else: ?>
        var txt = "<?php echo base_url(); ?>data/models/non-drivers/<?php echo $id; ?>/p<?php echo $nondrivers[0]; ?>/ranked_0.pdb";
        <?php endif; ?>

        /* ----------------------------------------------------------
            Função de coloração por pLDDT
        ---------------------------------------------------------- */
        const colorByPLDDT = function (atom) {
            const p = atom.b;               // Coluna B-factor
            if (p > 90)  return '#0053D6';  // Very high (pLDDT > 90)
            if (p > 70)  return '#4DA3FF';  // High (90 > pLDDT > 70)
            if (p > 50)  return '#FFDB4D';  // Low (70 > pLDDT > 50)
            if (p > 30)  return '#FF7B2D';  // Very low (pLDDT < 50)
            return '#FF4040';               
        };

        /* ----------------------------------------------------------
            Carrega o PDB
        ---------------------------------------------------------- */
        $.get(txt, function (d) {

            const glviewer = $3Dmol.createViewer("pdb", {
                defaultcolors: $3Dmol.rasmolElementColors
            });
            glviewer.setBackgroundColor(0xffffff);

            const model = glviewer.addModel(d, "pdb");

            glviewer.setStyle({}, { cartoon: { colorfunc: colorByPLDDT } });

            const atoms = model.selectedAtoms({});
            for (let i = 0; i < atoms.length; i++) {
                const atom = atoms[i];
                atom.clickable = true;
                atom.callback  = atomcallback;
            }

            glviewer.zoomTo();
            glviewer.render();
        });

        const atomcallback = function (atom, viewer) {
            if (!atom.clickLabel) {
                atom.clickLabel = viewer.addLabel(
                    `${atom.resn} ${atom.resi} (${atom.elem}) — pLDDT ${atom.b.toFixed(1)}`,
                    { fontSize: 10, position: { x: atom.x, y: atom.y, z: atom.z }, backgroundColor: "black" });
                atom.clicked = true;
            } else {
                // alterna entre apagar e destacar rótulo
                viewer.removeLabel(atom.clickLabel);
                atom.clickLabel = undefined;
                atom.clicked = false;
            }
        };
    });
</script>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
    // tooltips
    const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]');
    const tooltipList = [...tooltipTriggerList].map(tooltipTriggerEl => new bootstrap.Tooltip(tooltipTriggerEl));


    // MAPA DE CONTATOS
    let allChains = new Set();
    let allDataPoints = [];
    let scatterChart;
    let colorMap = {};
    const cat10Colors = [
        '#1f77b4', '#ff7f0e', '#2ca02c', '#d62728', '#9467bd',
        '#8c564b', '#e377c2', '#7f7f7f', '#bcbd22', '#17becf'
    ];

    function populateChainSelectors() {
        const chainX = document.getElementById('chainX');
        const chainY = document.getElementById('chainY');
        chainX.innerHTML = "";
        chainY.innerHTML = "";
        allChains.forEach(chain => {
            const optionX = document.createElement("option");
            optionX.value = optionX.textContent = chain;
            const optionY = document.createElement("option");
            optionY.value = optionY.textContent = chain;
            chainX.appendChild(optionX);
            chainY.appendChild(optionY);
        });
        chainX.value = 'A';
        chainY.value = 'A';
    }

    function updateChart() {
        const selectedX = document.getElementById('chainX').value;
        const selectedY = document.getElementById('chainY').value;
        const filteredData = allDataPoints.filter(p => p.c1 === selectedX && p.c2 === selectedY);

        scatterChart.data.datasets[0].data = filteredData;
        scatterChart.data.datasets[0].pointBackgroundColor = filteredData.map(p => p.backgroundColor);
        scatterChart.options.scales.x.title.text = `Chain ${selectedX}`;
        scatterChart.options.scales.y.title.text = `Chain ${selectedY}`;
        scatterChart.update();
    }

    function saveChart() {
        const canvas = document.getElementById('scatterChart');
        const link = document.createElement('a');
        link.href = canvas.toDataURL('image/png');
        link.download = 'contacts_<?= $id ?>.png';
        link.click();
    }

    // fetch('<?php echo base_url(); ?>data/pdb/<?= substr($id, 0, 1) ?>/<?= $id ?>/<?= $id ?>_contacts.csv')
    //     .then(response => response.text())
    //     .then(text => {
    //         const lines = text.split('\n').map(line => line.trim()).filter(line => line);
    //         lines.shift(); // Ignorar a primeira linha
    //         let colorIndex = 0;
    //         let legendHTML = "<strong>Caption:</strong>";

    //         lines.forEach(line => {
    //             const values = line.split(',');
    //             if (values.length >= 10) {
    //                 const c1 = values[0];
    //                 const x = parseFloat(values[1]);
    //                 const aa1 = values[2];
    //                 const at1 = values[3];
    //                 const c2 = values[4];
    //                 const y = parseFloat(values[5]);
    //                 const aa2 = values[6];
    //                 const at2 = values[7];
    //                 const category = values[9].trim();
    //                 const label = `${category} | ${c1}:${aa1}${x} (${at1}) - ${c2}:${aa2}${y} (${at2})`;

    //                 allChains.add(c1);
    //                 allChains.add(c2);

    //                 if (!colorMap[category]) {
    //                     colorMap[category] = cat10Colors[colorIndex % cat10Colors.length];
    //                     legendHTML += `<div style='display: flex; align-items: center; gap: 5px;'>
    //                 <div style='width: 20px; height: 20px; background-color: ${colorMap[category]};'></div>${category}</div>`;
    //                     colorIndex++;
    //                 }

    //                 allDataPoints.push({
    //                     x,
    //                     y,
    //                     c1,
    //                     c2,
    //                     backgroundColor: colorMap[category],
    //                     label
    //                 });
    //             }
    //         });

    //         document.getElementById('legend').innerHTML = legendHTML;
    //         populateChainSelectors();

    //         const ctx = document.getElementById('scatterChart').getContext('2d');
    //         scatterChart = new Chart(ctx, {
    //             type: 'scatter',
    //             data: {
    //                 datasets: [{
    //                     label: 'Dispersão CSV',
    //                     data: allDataPoints.filter(p => p.c1 === 'A' && p.c2 === 'A'),
    //                     pointBackgroundColor: allDataPoints.map(p => p.backgroundColor),
    //                     borderWidth: 0,
    //                     pointRadius: 5,
    //                     pointHoverRadius: 7,
    //                 }]
    //             },
    //             options: {
    //                 plugins: {
    //                     tooltip: {
    //                         callbacks: {
    //                             label: function(tooltipItem) {
    //                                 return tooltipItem.raw.label;
    //                             }
    //                         }
    //                     },
    //                     legend: {
    //                         display: false
    //                     }
    //                 },
    //                 scales: {
    //                     x: {
    //                         title: {
    //                             display: true,
    //                             text: 'Chain A'
    //                         },
    //                         beginAtZero: false,
    //                         min: 1,
    //                     },
    //                     y: {
    //                         title: {
    //                             display: true,
    //                             text: 'Chain A'
    //                         },
    //                         beginAtZero: false,
    //                         min: 1,
    //                     }
    //                 }
    //             }
    //         });


    //     })
    //     .catch(error => console.error('Erro ao carregar o arquivo CSV:', error));
</script>
<?= $this->endSection() ?>