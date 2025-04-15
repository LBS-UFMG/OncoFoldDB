<?= $this->extend('template') ?>
<?= $this->section('conteudo') ?>
<!-- Conteúdo personalizado -->

<div class="container-fluid py-5">

    <h1 class="pb-5 text-dark">Download</h1>
    
    <table class="table">
        <thead>
            <tr>
                <th>File</th>
                <th>Size</th>
                <th>Description</th>
                <th>Download</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td><b>Full database</b></td>
                <td>43M</td>
                <td>PDBs in CIF format (zip file)</td>
                <td><a href="<?=base_url('/data/db.zip')?>" target="_blank">db.zip</a></td>
            </tr>

            
        </tbody>
    </table>
</div>
<!-- / FIM Conteúdo personalizado -->
<?= $this->endSection() ?>
