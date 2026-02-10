<h2>Cultius</h2>

<div class="form-section">
    <h3>Afegir nou cultiu</h3>
    <form method="post">
        <input type="hidden" name="p" value="cultius">
        <input type="hidden" name="nou_cultiu" value="1">
        Nom comú: <input type="text" name="nom_comu" required><br><br>
        Nom científic: <input type="text" name="nom_cientific"><br><br>
        Cicle vegetatiu: <input type="text" name="cicle_vegetatiu"><br><br>
        Qualitats fruit: <textarea name="qualitats_fruit"></textarea><br><br>
        <button type="submit" class="btn">Afegir cultiu</button>
    </form>
</div>

<?php if ($cultius_list->num_rows > 0): ?>
    <table>
        <tr>
            <th>ID</th>
            <th>Nom comú</th>
            <th>Nom científic</th>
            <th>Cicle</th>
            <th>Qualitats</th>
            <th>Acció</th>
        </tr>
        <?php while($c = $cultius_list->fetch_assoc()): ?>
            <tr>
                <td><?= $c['id_cultiu'] ?></td>
                <td><?= htmlspecialchars($c['nom_comu']) ?></td>
                <td><?= htmlspecialchars($c['nom_cientific'] ?? '-') ?></td>
                <td><?= htmlspecialchars($c['cicle_vegetatiu'] ?? '-') ?></td>
                <td><?= htmlspecialchars($c['qualitats_fruit'] ?? '-') ?></td>
                <td>
                    <a href="?eliminar=1&tipus=cultiu&id=<?= $c['id_cultiu'] ?>&p=cultius" 
                       class="btn btn-red" 
                       onclick="return confirm('Segur que vols eliminar aquest cultiu?');">
                        Eliminar
                    </a>
                </td>
            </tr>
        <?php endwhile; ?>
    </table>
<?php else: ?>
    <p>Encara no hi ha cultius.</p>
<?php endif; ?>

<h2 style="margin-top:60px;">Sectors de cultiu</h2>
<div class="form-section">
    <form method="post">
        <input type="hidden" name="p" value="cultius">
        <input type="hidden" name="nou_sector" value="1">
        Parcel·la:
        <select name="id_parcela" required>
            <option value="">Selecciona</option>
            <?php while($pr = $parceles_select->fetch_assoc()): ?>
                <option value="<?= $pr['id_parcela'] ?>"><?= htmlspecialchars($pr['nom']) ?></option>
            <?php endwhile; $parceles_select->data_seek(0); ?>
        </select><br><br>
        Data plantació: <input type="date" name="data_plantacio"><br><br>
        Marc arbres: <input type="text" name="marc_plantacio_arbres" required placeholder="ex: 5x5 m"><br><br>
        Marc files: <input type="text" name="marc_plantacio_files" required placeholder="ex: 4x4 m"><br><br>
        Nº arbres: <input type="number" name="num_arbres" min="0" required><br><br>
        Previsió producció (kg/ha): <input type="number" name="previsio_produccio" step="0.01" min="0" required><br><br>
        <button type="submit" class="btn">Afegir sector</button>
    </form>
</div>

<?php if ($sectors_cultiu_list->num_rows > 0): ?>
    <table>
        <tr>
            <th>ID</th>
            <th>Parcel·la</th>
            <th>Data plantació</th>
            <th>Marc arbres</th>
            <th>Marc files</th>
            <th>Nº arbres</th>
            <th>Previsió</th>
            <th>Acció</th>
        </tr>
        <?php while($s = $sectors_cultiu_list->fetch_assoc()): ?>
            <tr>
                <td><?= $s['id_sector'] ?></td>
                <td><?= htmlspecialchars($s['nom_parcela']) ?></td>
                <td><?= $s['data_plantacio'] ? date('d/m/Y', strtotime($s['data_plantacio'])) : '-' ?></td>
                <td><?= htmlspecialchars($s['marc_plantacio_arbres']) ?></td>
                <td><?= htmlspecialchars($s['marc_plantacio_files']) ?></td>
                <td><?= $s['num_arbres'] ?></td>
                <td><?= number_format($s['previsio_produccio'], 2) ?></td>
                <td>
                    <a href="?eliminar=1&tipus=sector&id=<?= $s['id_sector'] ?>&p=cultius" 
                       class="btn btn-red" 
                       onclick="return confirm('Segur?');">
                        Eliminar
                    </a>
                </td>
            </tr>
        <?php endwhile; ?>
    </table>
<?php else: ?>
    <p>Encara no hi ha sectors.</p>
<?php endif; ?>

<h2 style="margin-top:60px;">Files d'arbres</h2>
<div class="form-section">
    <form method="post">
        <input type="hidden" name="p" value="cultius">
        <input type="hidden" name="nou_fila" value="1">
        Sector:
        <select name="id_sector" required>
            <option value="">Selecciona</option>
            <?php while($s = $sectors_select->fetch_assoc()): ?>
                <option value="<?= $s['id_sector'] ?>"><?= htmlspecialchars($s['nom_parcela']) ?></option>
            <?php endwhile; $sectors_select->data_seek(0); ?>
        </select><br><br>
        Número fila: <input type="number" name="numero_fila" min="1" required><br><br>
        Coordenades fila: <input type="text" name="coordenades_fila" required placeholder="ex: 41.12345, 1.67890"><br><br>
        Notes: <textarea name="notes"></textarea><br><br>
        <button type="submit" class="btn">Afegir fila</button>
    </form>
</div>

<?php if ($files_arbres_list->num_rows > 0): ?>
    <table>
        <tr>
            <th>ID</th>
            <th>Sector</th>
            <th>Número fila</th>
            <th>Coordenades</th>
            <th>Notes</th>
            <th>Acció</th>
        </tr>
        <?php while($f = $files_arbres_list->fetch_assoc()): ?>
            <tr>
                <td><?= $f['id_fila'] ?></td>
                <td><?= htmlspecialchars($f['nom_parcela']) ?></td>
                <td><?= $f['numero_fila'] ?></td>
                <td><?= htmlspecialchars($f['coordenades_fila'] ?? '-') ?></td>
                <td><?= htmlspecialchars($f['notes'] ?? '-') ?></td>
                <td>
                    <a href="?eliminar=1&tipus=fila&id=<?= $f['id_fila'] ?>&p=cultius" 
                       class="btn btn-red" 
                       onclick="return confirm('Segur?');">
                        Eliminar
                    </a>
                </td>
            </tr>
        <?php endwhile; ?>
    </table>
<?php else: ?>
    <p>Encara no hi ha files d'arbres.</p>
<?php endif; ?>
