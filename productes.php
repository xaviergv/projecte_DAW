<div class="section">
    <!-- 1. Afegir nou producte -->
    <div class="form-section">
        <h3><i class="fa-solid fa-box" style="color:var(--primary); margin-right:8px;"></i> Afegir nou producte</h3>
        <form method="post">
            <input type="hidden" name="p" value="productes">
            <input type="hidden" name="nou_producte" value="1">

            <div class="form-grid">
                <div class="form-group">
                    <label>Nom comercial:</label>
                    <input type="text" name="nom_comercial" required placeholder="ex: Cobrex 50">
                </div>

                <div class="form-group">
                    <label>Tipus:</label>
                    <select name="tipus" required>
                        <option value="">Selecciona</option>
                        <option value="Fertilitzant">Fertilitzant</option>
                        <option value="Insecticida">Insecticida</option>
                        <option value="Fungicida">Fungicida</option>
                        <option value="Herbicida">Herbicida</option>
                    </select>
                </div>

                <div class="form-group">
                    <label>Dosi recomanada:</label>
                    <input type="text" name="dosi_recomanada" required placeholder="ex: 2 L/ha">
                </div>

                <div class="form-group">
                    <label>Quantitat mínima d'avís:</label>
                    <input type="number" name="quantitat_minima" step="0.01" min="0" value="10" required>
                </div>
            </div>

            <button type="submit" class="btn"><i class="fa-solid fa-plus"></i> Afegir producte</button>
        </form>
    </div>

    <!-- Llistat de productes amb Eliminar -->
    <h3><i class="fa-solid fa-list-ul" style="margin-right:8px; color:var(--text-muted);"></i> Llistat de productes registrats</h3>
    <?php
    $productes = $conn->query("
        SELECT id_producte, nom_comercial, tipus, dosi_recomanada, quantitat_minima
        FROM Producte 
        ORDER BY nom_comercial
    ");

    if ($productes->num_rows > 0):
    ?>
        <div class="table-container">
            <table>
                <tr>
                    <th>ID</th>
                    <th>Nom comercial</th>
                    <th>Tipus</th>
                    <th>Dosi recomanada</th>
                    <th>Mínim d'avís</th>
                    <th>Acció</th>
                </tr>
                <?php while ($pr = $productes->fetch_assoc()): ?>
                    <tr>
                        <td><strong>#<?= $pr['id_producte'] ?></strong></td>
                        <td><?= htmlspecialchars($pr['nom_comercial']) ?></td>
                        <td>
                            <?php
                            $bdg = 'badge-secondary';
                            if($pr['tipus'] == 'Fertilitzant') $bdg = 'badge-success';
                            if($pr['tipus'] == 'Insecticida') $bdg = 'badge-danger';
                            if($pr['tipus'] == 'Fungicida') $bdg = 'badge-info';
                            if($pr['tipus'] == 'Herbicida') $bdg = 'badge-warning';
                            echo "<span class='badge $bdg'>" . htmlspecialchars($pr['tipus']) . "</span>";
                            ?>
                        </td>
                        <td><?= htmlspecialchars($pr['dosi_recomanada']) ?></td>
                        <td><?= number_format($pr['quantitat_minima'], 2) ?></td>
                        <td>
                            <a href="?p=productes&eliminar_producte=1&id=<?= $pr['id_producte'] ?>"
                               class="btn btn-red btn-icon" onclick="return confirm('Segur que vols eliminar aquest producte?');" title="Eliminar">
                                <i class="fa-solid fa-trash-can"></i>
                            </a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </table>
        </div>
    <?php else: ?>
        <p style="color:var(--text-muted);"><i class="fa-solid fa-folder-open"></i> Encara no hi ha productes registrats.</p>
    <?php endif; ?>

    <hr style="border: 0; height: 1px; background: var(--border-color); margin: 40px 0;">

    <!-- 2. Afegir estoc -->
    <div class="form-section">
        <h3><i class="fa-solid fa-truck-ramp-box" style="color:var(--warning); margin-right:8px;"></i> Afegir entrada d'estoc</h3>
        <form method="post">
            <input type="hidden" name="p" value="productes">
            <input type="hidden" name="nou_estoc" value="1">

            <div class="form-grid">
                <div class="form-group">
                    <label>Producte:</label>
                    <select name="id_producte" required>
                        <option value="">Selecciona producte</option>
                        <?php
                        $prods = $conn->query("SELECT id_producte, nom_comercial FROM Producte ORDER BY nom_comercial");
                        while ($pr = $prods->fetch_assoc()) {
                            echo '<option value="' . $pr['id_producte'] . '">' . htmlspecialchars($pr['nom_comercial']) . '</option>';
                        }
                        ?>
                    </select>
                </div>

                <div class="form-group">
                    <label>Quantitat entrante:</label>
                    <input type="number" name="quantitat_disponible" step="0.01" min="0" required placeholder="ex: 50.5">
                </div>

                <div class="form-group">
                    <label>Unitat de mesura:</label>
                    <input type="text" name="unitat_mesura" required placeholder="ex: kg, L">
                </div>
            </div>

            <button type="submit" class="btn" style="background:var(--warning); color:#fff;"><i class="fa-solid fa-cubes-stacked"></i> Afegir estoc</button>
        </form>
    </div>

    <!-- Llistat d'estoc amb Eliminar -->
    <h3><i class="fa-solid fa-warehouse" style="margin-right:8px; color:var(--text-muted);"></i> Moviments d'inventari</h3>
    <?php
    $estoc = $conn->query("
        SELECT e.id_estoc, p.nom_comercial, e.quantitat_disponible, e.unitat_mesura, 
               e.data_compra, e.proveidor, e.numero_lot, e.data_caducitat, e.ubicacio_magatzem
        FROM Estoc e
        JOIN Producte p ON e.id_producte = p.id_producte
        ORDER BY e.id_estoc DESC
    ");

    if ($estoc->num_rows > 0):
    ?>
        <div class="table-container">
            <table>
                <tr>
                    <th>ID</th>
                    <th>Producte</th>
                    <th>Quantitat</th>
                    <th>Data compra</th>
                    <th>Proveïdor</th>
                    <th>Lot / Caducitat</th>
                    <th>Ubicació</th>
                    <th>Acció</th>
                </tr>
                <?php while ($e = $estoc->fetch_assoc()): ?>
                    <tr>
                        <td><strong>#<?= $e['id_estoc'] ?></strong></td>
                        <td><?= htmlspecialchars($e['nom_comercial']) ?></td>
                        <td><span class="badge badge-success" style="font-size:0.85rem;"><?= number_format($e['quantitat_disponible'], 2) ?> <?= htmlspecialchars($e['unitat_mesura']) ?></span></td>
                        <td><?= htmlspecialchars($e['data_compra'] ?? '-') ?></td>
                        <td><?= htmlspecialchars($e['proveidor'] ?? '-') ?></td>
                        <td>
                            Lot: <?= htmlspecialchars($e['numero_lot'] ?? '-') ?><br>
                            Cad: <?= htmlspecialchars($e['data_caducitat'] ?? '-') ?>
                        </td>
                        <td><?= htmlspecialchars($e['ubicacio_magatzem'] ?? '-') ?></td>
                        <td>
                            <a href="?p=productes&eliminar_estoc=1&id=<?= $e['id_estoc'] ?>"
                               class="btn btn-red btn-icon" onclick="return confirm('Segur que vols eliminar aquesta entrada d\'estoc?');" title="Eliminar">
                                <i class="fa-solid fa-trash-can"></i>
                            </a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </table>
        </div>
    <?php else: ?>
        <p style="color:var(--text-muted);"><i class="fa-solid fa-folder-open"></i> Encara no hi ha entrades d'estoc.</p>
    <?php
    endif;
    if (isset($_POST['nou_producte'])) {
        $nom_comercial = trim($_POST['nom_comercial'] ?? '');
        $tipus = trim($_POST['tipus'] ?? '');
        $dosi_recomanada = trim($_POST['dosi_recomanada'] ?? '');
        $quantitat_minima = (float)($_POST['quantitat_minima'] ?? 0);

        if ($nom_comercial && $tipus) {
            $stmt = $conn->prepare("INSERT INTO Producte (nom_comercial, tipus, dosi_recomanada, quantitat_minima) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("sssd", $nom_comercial, $tipus, $dosi_recomanada, $quantitat_minima);
            $stmt->execute();
            $stmt->close();
            $_SESSION['msg'] = "Producte creat correctament!";
        } else {
            $_SESSION['err'] = "Emplena el nom i el tipus.";
        }
    }

    if (isset($_POST['nou_estoc'])) {
        $id_producte = (int)($_POST['id_producte'] ?? 0);
        $quantitat_disponible = (float)($_POST['quantitat_disponible'] ?? 0);
        $unitat_mesura = trim($_POST['unitat_mesura'] ?? '');

        if ($id_producte > 0 && $quantitat_disponible > 0) {
            $stmt = $conn->prepare("INSERT INTO Estoc (id_producte, quantitat_disponible, unitat_mesura, data_compra) VALUES (?, ?, ?, CURDATE())");
            $stmt->bind_param("ids", $id_producte, $quantitat_disponible, $unitat_mesura);
            $stmt->execute();
            $stmt->close();
            $_SESSION['msg'] = "Estoc restablit correctament!";
        } else {
            $_SESSION['err'] = "Producte i quantitat oblligatoris.";
        }
    }
    ?>
</div>
