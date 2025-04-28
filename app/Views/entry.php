<?= $this->extend('template') ?>
<?= $this->section('conteudo') ?>

<!-- Custom content -->
<link rel="stylesheet" href="<?= base_url('/css/dt.css'); ?>">
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>

<!-- Loading overlay ------------------------------------------------------->
<div id="loading">
    <div class="text-center">
        <img src="<?= base_url('/img/cocadito-loading.png') ?>" width="200px"><br>
        <div class="spinner-border spinner-border-sm" role="status"></div>
        <strong class="ms-2">Loading...</strong>
    </div>
</div>

<!-- Header band ----------------------------------------------------------->
<div style="background-color:#e4e4e4; height:180px; margin:-25px -10px 20px -10px;">
    <div class="container-fluid px-5">
        <div class="row">
            <div class="col-md-9 col-xs-12 pt-2">
                <h2 class="title_h2 pt-4">
                    <strong><?= $gene_id; ?></strong>
                </h2>

                <h4><?= $gene_name; ?></h4>

                <p class="mb-0 mt-3">
                    <strong>Driver Mutations Count:</strong>
                    <?= count(array_filter($drivers, fn ($v) => trim($v) === '')) > 0 ? 0 : count($drivers); ?>
                    <span class="px-4">|</span>
                    <strong>Passenger Mutations Count:</strong>
                    <?= count(array_filter($nondrivers, fn ($v) => trim($v) === '')) > 0 ? 0 : count($nondrivers); ?>
                </p>
            </div>
        </div>
    </div>
</div>

<!-- Main layout ----------------------------------------------------------->
<div class="container-fluid px-5">
    <div class="row">
        <!-- Mutation table -------------------------------------------------->
        <div class="col-md-6" ng-if="cttlok">
            <br>
            <div class="table-responsive">
                <table class="display" id="mut">
                    <thead>
                        <tr>
                            <th class="dt-center">Mutation&nbsp;(HGVS)</th>
                            <th class="dt-center">Variant Classification</th>
                            <th class="dt-center">PDB Download</th>
                            <th class="dt-center">Reference</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                            // Build a unified list of mutations with their type
                            $mutations = [];
                            foreach ($drivers     as $d) $mutations[] = ['mutation' => $d, 'type' => 'Driver'];
                            foreach ($nondrivers  as $d) $mutations[] = ['mutation' => $d, 'type' => 'Passenger'];

                            foreach ($mutations as $m):
                                if (trim($m['mutation']) === '') continue;

                                $mutation = htmlspecialchars($m['mutation']);
                                $type     = $m['type'];
                                $color    = $type === 'Driver' ? 'bg-danger' : 'bg-primary';
                                $pdb_path = $type === 'Driver' ? 'drivers'  : 'non-drivers';
                        ?>
                        <tr onclick="selectID(glviewer,
                                              this.children[0].innerHTML,
                                              1,
                                              this.children[1].innerHTML,
                                              this.children[3]?.innerHTML ?? '',
                                              this.children[6]?.innerHTML ?? '')"
                            id="<?= $mutation ?>">
                            <td class="fw-semibold"><?= "p.$mutation" ?></td>
                            <td class="<?= $color ?> text-white"><?= $type ?></td>
                            <td class="text-center">
                                <a href="<?= base_url("data/models/$pdb_path/$id/p$mutation/ranked_0.pdb") ?>">
                                    <i class="bi bi-download"></i>
                                </a>
                            </td>
                            <td class="text-center" data-ref><!-- filled via JS --></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- 3Dmol viewer ---------------------------------------------------->
        <div class="col-md-6">
            <style>
                .affix { top: 100px; z-index: 9999 !important; }
                #pdb canvas { position: relative !important; }
            </style>

            <div data-spy="affix" id="affix" data-offset-top="240" data-offset-bottom="250">
                <div id="pdb" style="min-height: 400px; height: 50vh; min-width:280px; width: 100%"></div>
                <p style="color:#ccc; text-align:right;">Wild protein</p>
            </div>
        </div>
    </div>
</div>

<!-- Return-to-top anchor --------------------------------------------------->
<a href="#" title="Return to top"
   style="position:fixed; right:10px; bottom:10px; color:#cccccc77">
    <span class="glyphicon glyphicon-chevron-up small" aria-hidden="true">Return to top</span>
</a>

<!-- JavaScript ------------------------------------------------------------>
<script>
    let glviewer;

    /* ---------------------------------------------------------------------
       Color cartoon by pLDDT (B-factor column stores pLDDT for AlphaFold)
    --------------------------------------------------------------------- */
    function colorByPLDDT(atom) {
        const p = atom.b;
        if (p > 90) return '#0053D6';   // very high
        if (p > 70) return '#4DA3FF';   // high
        if (p > 50) return '#FFDB4D';   // medium
        if (p > 30) return '#FF7B2D';   // low
        return '#FF4040';               // very low (<30)
    }

    /* ---------------------------------------------------------------------
       DataTable with mutations
    --------------------------------------------------------------------- */
    $(() => {
        $.get("<?= base_url('data/mutation_table.csv') ?>")
            .done(raw => {
                const Gene = s => s.trim().toUpperCase();
                const Mut  = s => s.trim().replace(/^p\./i, '').toUpperCase();
                const map = {};

                raw.trim().split('\n').forEach(line => {
                    const [gene, mut, url] = line.split(/\s+/);
                    if (!gene || !mut || !url) return;

                    const geneKey = Gene(gene);
                    const mutKey  = Mut(mut);

                    map[geneKey]         ??= {};
                    map[geneKey][mutKey]  = url;
                });

                const gene = Gene('<?= $gene_id ?>');

                function fillReferenceColumn() {
                    $('#mut tbody tr').each(function () {
                        const $cells = $(this).children('td');
                        const mut   = Mut($cells.eq(0).text());
                        const url   = map[gene]?.[mut];

                        $cells.eq(3).html(
                            url ? `<a target="_blank" href="${url}">
                                        <i class="bi bi-journal-text"></i>
                                   </a>` : '–'
                        );
                    });
                }

                if (!$.fn.DataTable.isDataTable('#mut')) {
                    $('#mut').DataTable({ order: [], paging: true });
                }

                fillReferenceColumn();

                $('#mut').on('draw.dt', () => fillReferenceColumn());
            })
            .fail(err => console.error('Error reading mutation_table.csv.', err));
    });

    /* ---------------------------------------------------------------------
        General functions
    ------------------------------------------------------------------------ */

    // Highlight a given row
    function highlight(pos) {
        $(pos).css("background-color", "#f2dede");
    }

    // Fade out loading screen
    $(() => setTimeout(() => $('#loading').fadeOut(), 1000));

    // Keep nav fixed inside the view container
    $('nav').css('position', 'relative');

    /* ---------------------------------------------------------------------
       3Dmol: select a residue by HGVS string
    --------------------------------------------------------------------- */
    function selectID(glviewer, residue /* p.X123Y */, type, chain) {
        if (!glviewer) return;

        residue = residue.replace(/^p\./i, '');   // remove "p." prefix
        const resiNum = (residue.match(/\d+/) || [])[0];
        if (!resiNum) return;

        // Reset whole model
        glviewer.setStyle({}, {
            cartoon: { colorfunc: colorByPLDDT },
            line:    { color: 'grey' }
        });

        // Highlight selected residue
        glviewer.setStyle(
            { resi: resiNum, chain: 'A' },
            { stick: { colorscheme: 'whiteCarbon' } }
        );

        glviewer.zoomTo({ resi: resiNum, chain: 'A' });
        glviewer.render();
    }

    /* ---------------------------------------------------------------------
       Load initial PDB
    --------------------------------------------------------------------- */
    $(document).ready(function () {
        <?php if (count($drivers) > 0): ?>
            const pdbURL = "<?= base_url(); ?>data/models/drivers/<?= $id; ?>/p<?= $drivers[0]; ?>/ranked_0.pdb";
        <?php else: ?>
            const pdbURL = "<?= base_url(); ?>data/models/non-drivers/<?= $id; ?>/p<?= $nondrivers[0]; ?>/ranked_0.pdb";
        <?php endif; ?>

        $.get(pdbURL, function (data) {
            glviewer = $3Dmol.createViewer("pdb", {
                defaultcolors: $3Dmol.rasmolElementColors,
            });

            glviewer.setBackgroundColor(0xffffff);
            const model = glviewer.addModel(data, "pdb");
            glviewer.setStyle({}, { cartoon: { colorfunc: colorByPLDDT } });

            // Make atoms clickable
            const atoms = model.selectedAtoms({});
            const atomCallback = function (atom, viewer) {
                if (!atom.clickLabel) {
                    atom.clickLabel = viewer.addLabel(
                        `${atom.resn} ${atom.resi} (${atom.elem}) — pLDDT ${atom.b.toFixed(1)}`,
                        {
                            fontSize: 10,
                            position: { x: atom.x, y: atom.y, z: atom.z },
                            backgroundColor: "black"
                        }
                    );
                    atom.clicked = true;
                } else {
                    viewer.removeLabel(atom.clickLabel);
                    delete atom.clickLabel;
                    atom.clicked = false;
                }
            };
            atoms.forEach(atom => {
                atom.clickable = true;
                atom.callback  = atomCallback;
            });

            glviewer.zoomTo();
            glviewer.render();
        });
    });
</script>

<?= $this->endSection() ?>

<!-- Optional extra scripts section --------------------------------------->
<?= $this->section('scripts') ?>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<!--
    Contact-map plotting code is left here (commented) for future use.
    Comments inside that block were also translated to English but have
    been kept disabled so functionality remains identical.
-->
<?= $this->endSection() ?>
