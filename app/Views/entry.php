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
                            // Unified list of mutations
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
                        <tr onclick="selectResidue(glviewer, this.children[0].innerHTML, this.children[1].innerHTML)"
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
            <div id="seq_box"
                 style="font-family:monospace;white-space:pre-wrap;
                        max-height:130px;overflow-y:auto;cursor:pointer;
                        border:1px solid #ddd;padding:4px;margin-bottom:8px;
                        display:none"></div>
                <div id="pdb" style="min-height: 400px; height: 50vh; min-width:280px; width: 100%"></div>
                <p style="color:#ccc; text-align:right;">AlphaFold Model</p>
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

    /* ---------------------------------------------------------------------
        General functions
    ------------------------------------------------------------------------ */
    let glviewer;

    let currentSel = null;
    let currentSpan = null;
    let currentLabel = null;
    
    // Highlight sequence in HTML
    function markSequenceResidue(idx) {
        const resi = idx + 1;

        // Clear previous styles
        if (currentSel) glviewer.setStyle(currentSel, {});
        if (currentLabel) {
            glviewer.removeLabel(currentLabel);
            currentLabel = null;
        }
        if (currentSpan) currentSpan.classList.remove('selected');

        // Apply selection style
        currentSel = { resi: resi, chain: 'A' };
        glviewer.setStyle(currentSel, {
            stick: { radius: 0.2, colorscheme: 'default' }
        });

        const model = glviewer.getModel();
        const atoms = model.selectedAtoms(currentSel);
        if (atoms.length > 0) {
            const atom = atoms[0];
            const resName = atom.resn;
            const resNum = atom.resi;
            const plddt = atom.b ? atom.b.toFixed(1) : 'N/A';

            // Add label with residue info
            const labelText = `${resName} ${resNum}\npLDDT: ${plddt}`;
            currentLabel = glviewer.addLabel(labelText, {
                fontSize: 12,
                fontColor: 'black',
                backgroundColor: 'white',
                backgroundOpacity: 0.8,
                inFront: true,
                position: { x: atom.x, y: atom.y, z: atom.z + 1.5 }
            });
        }

        glviewer.zoomTo(currentSel);
        glviewer.render();

        const span = document.getElementById(`res${idx}`);
        if (span) {
            span.classList.add('selected');
            span.scrollIntoView({ behavior: 'smooth', block: 'center' });
            currentSpan = span;
        }
    }

    // Render FASTA sequence
    function renderSequence(seq) {
        const lineLength = 60;
        const blockSize = 10;
        let html = '';

        for (let i = 0; i < seq.length; i += lineLength) {
            const line = seq.slice(i, i + lineLength);

            // Position header
            let header = '';
            for (let j = blockSize; j <= line.length; j += blockSize) {
                const pos = i + j;
                header += ' '.repeat(blockSize - String(pos).length) + '   ' + pos + ' ';
            }
            html += `<div class="seq-header">${header.trimEnd()}</div>`;

            // Protein sequence
            let seqLine = '';
            for (let j = 0; j < line.length; j++) {
                const globalIdx = i + j;
                seqLine += `<span id="res${globalIdx}" onclick="markSequenceResidue(${globalIdx})"
                            title="Residue ${globalIdx + 1}">${line[j]}</span>`;
                if ((j + 1) % blockSize === 0) seqLine += ' ';
            }

            html += `<div>${seqLine.trimEnd()}</div>`;
        }

        $('#seq_box').html(html);
    }

    // Color cartoon by pLDDT (B-factor column stores pLDDT for AlphaFold)
    function colorByPLDDT(atom) {                   
        const p = atom.b;
        if (p > 90) return '#0053D6';   // very high
        if (p > 70) return '#4DA3FF';   // high
        if (p > 50) return '#FFDB4D';   // medium
        if (p > 30) return '#FF7B2D';   // low
        return '#FF4040';               // very low (<30)
    }

    // Fade out loading screen
    $(() => setTimeout(() => $('#loading').fadeOut(), 1000));

    // Keep nav fixed inside the view container
    $('nav').css('position', 'relative');

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
       3Dmol: select a residue by HGVS string
    --------------------------------------------------------------------- */
    function selectResidue(glviewer, residue, type) {
        if (!glviewer) return;

        const resiText = residue.replace(/^p\./i, '');
        const index = parseInt((resiText.match(/\d+/) || [])[0])-1;
        if (!index) return;

        markSequenceResidue(index);

        const gene = "<?= $id ?>";
        const mut = resiText;
        const fastaPath = (type.trim().toLowerCase() === 'driver') ? 'drivers' : 'non-drivers';
        const fastaURL = `<?= base_url() ?>data/fastas/${fastaPath}/${gene}_mutated_p${mut}.fasta`;

        $.get(fastaURL).done(fasta => {
            const lines = fasta.trim().split('\n');
            const sequence = lines.slice(1).join('').trim();

            // Render sequence
            renderSequence(sequence);

            // Update the DOM
            setTimeout(() => {
                const span = document.getElementById(`res${index}`);
                if (span) {
                    span.classList.add('selected');
                    span.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    currentSpan = span;
                }
            }, 1);

            $('#seq_box').show();
        }).fail(() => {
            $('#seq_box').html('<i style="color:#a00">FASTA not found for selected mutation.</i>');
        });
    }

    /* ---------------------------------------------------------------------
       Load initial PDB
    --------------------------------------------------------------------- */
    $(document).ready(function () {

    <?php if (count($drivers) > 0): ?>
    const pdbURL  = "<?= base_url(); ?>data/models/drivers/<?= $id; ?>/p<?= $drivers[0]; ?>/ranked_0.pdb";
    <?php else: ?>
    const pdbURL  = "<?= base_url(); ?>data/models/non-drivers/<?= $id; ?>/p<?= $nondrivers[0]; ?>/ranked_0.pdb";
    <?php endif; ?>

    $.get(pdbURL).done(data => {

        // Create the 3D viewer
        glviewer = $3Dmol.createViewer("pdb", { backgroundColor: '#fff' });
        const model = glviewer.addModel(data, "pdb");
        glviewer.setStyle({}, { cartoon: { colorfunc: colorByPLDDT } });
        glviewer.zoomTo();
        glviewer.render();

        // Extract sequence from the model
        const atoms = model.selectedAtoms({});
        const seq = [];                         
        atoms.forEach(a => {
            const i = a.resi - 1;               
            seq[i] = a.resn[0];                 
        });
        const sequence = seq.join('');

        renderSequence(sequence);              
        $('#seq_box').show();                  
    });

});

</script>

<style>
#seq_box {
    font-family: 'Courier New', monospace;
    white-space: pre;
    background-color: #fafafa;
    border: 1px solid #ddd;
    border-radius: 6px;
    padding: 12px 16px;
    max-height: 220px;
    overflow-y: auto;
    font-size: 12px;
    line-height: 1.6;
}

.seq-header {
    color: #999;
    font-size: 12px;
    margin-bottom: 2px;
    user-select: none;
}

#seq_box span {
    cursor: pointer;
    border-radius: 3px;
    padding: 0 1px;
    transition: background-color 0.2s ease;
}

#seq_box span:hover {
    background-color: #e6f0ff;
}

#seq_box span.selected {
    background-color: #ffdddd;
    font-weight: bold;
    color: #a00000;
}

</style>

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
