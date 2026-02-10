<div class="section">
    <h2>Parcel·les</h2>

    <div class="form-section">
        <h3>Afegir nova parcel·la</h3>
        <form method="post">
            <input type="hidden" name="p" value="parceles">
            <input type="hidden" name="nou_parcela" value="1">
            Nom: <input type="text" name="nom" required><br><br>
            Superfície (ha): <input type="number" name="superficie" step="0.01" required><br><br>
            Coordenades: <input type="text" name="coordenades"><br><br>
            Textura: <input type="text" name="textura"><br><br>
            <button type="submit" class="btn">Afegir parcel·la</button>
        </form>
    </div>

    <?php if ($parceles_list->num_rows > 0): ?>
        <table>
            <tr>
                <th>ID</th>
                <th>Nom</th>
                <th>Superfície</th>
                <th>Coordenades</th>
                <th>Textura</th>
                <th>Acció</th>
            </tr>
            <?php while($row = $parceles_list->fetch_assoc()): ?>
                <tr>
                    <td><?= $row['id_parcela'] ?></td>
                    <td><?= htmlspecialchars($row['nom']) ?></td>
                    <td><?= number_format($row['superficie'], 2) ?></td>
                    <td><?= htmlspecialchars($row['coordenades'] ?? '-') ?></td>
                    <td><?= htmlspecialchars($row['textura'] ?? '-') ?></td>
                    <td>
                        <a href="?eliminar=1&tipus=parcela&id=<?= $row['id_parcela'] ?>&p=parceles" 
                           class="btn btn-red" 
                           onclick="return confirm('Segur que vols eliminar aquesta parcel·la?');">
                            Eliminar
                        </a>
                    </td>
                </tr>
            <?php endwhile; ?>
        </table>
    <?php else: ?>
        <p>Encara no hi ha parcel·les.</p>
    <?php endif; ?>

    <h2 style="margin-top:60px;">Varietats</h2>

    <div class="form-section">
        <h3>Afegir nova varietat</h3>
        <form method="post">
            <input type="hidden" name="p" value="parceles">
            <input type="hidden" name="nova_varietat" value="1">
            Cultiu:
            <select name="id_cultiu" required>
                <option value="">Selecciona</option>
                <?php 
                $cultius_select->data_seek(0); 
                while($c = $cultius_select->fetch_assoc()): ?>
                    <option value="<?= $c['id_cultiu'] ?>"><?= htmlspecialchars($c['nom_comu']) ?></option>
                <?php endwhile; ?>
            </select><br><br>
            Nom varietat: <input type="text" name="nom_varietat" required><br><br>
            Característiques: <textarea name="caracteristiques"></textarea><br><br>
            <button type="submit" class="btn">Afegir varietat</button>
        </form>
    </div>

    <?php if ($varietats_list->num_rows > 0): ?>
        <table>
            <tr>
                <th>ID</th>
                <th>Cultiu</th>
                <th>Varietat</th>
                <th>Característiques</th>
                <th>Acció</th>
            </tr>
            <?php while($v = $varietats_list->fetch_assoc()): ?>
                <tr>
                    <td><?= $v['id_varietat'] ?></td>
                    <td><?= htmlspecialchars($v['cultiu']) ?></td>
                    <td><?= htmlspecialchars($v['nom_varietat']) ?></td>
                    <td><?= htmlspecialchars($v['caracteristiques'] ?? '-') ?></td>
                    <td>
                        <a href="?eliminar=1&tipus=varietat&id=<?= $v['id_varietat'] ?>&p=parceles" 
                           class="btn btn-red" 
                           onclick="return confirm('Segur que vols eliminar aquesta varietat?');">
                            Eliminar
                        </a>
                    </td>
                </tr>
            <?php endwhile; ?>
        </table>
    <?php else: ?>
        <p>Encara no hi ha varietats.</p>
    <?php endif; ?>
</div>
