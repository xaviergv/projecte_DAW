<div class="section">
    <!-- Formulari per afegir nova parcel·la -->
    <div class="form-section">
        <h3><i class="fa-solid fa-map-location-dot" style="color:var(--primary); margin-right:8px;"></i> Afegir nova parcel·la</h3>
        <form method="post">
            <input type="hidden" name="p" value="parceles">
            <input type="hidden" name="nou_parcela" value="1">

            <div class="form-grid">
                <div class="form-group">
                    <label>Nom de la parcel·la:</label>
                    <input type="text" name="nom" required placeholder="ex: Can Xifra">
                </div>

                <div class="form-group">
                    <label>Superfície (ha):</label>
                    <input type="number" name="superficie" step="0.01" min="0" required placeholder="ex: 2.5">
                </div>

                <div class="form-group">
                    <label>Coordenades (lat, lon):</label>
                    <input type="text" name="coordenades" placeholder="ex: 41.3851, 2.1734">
                </div>

                <div class="form-group">
                    <label>Textura del sòl:</label>
                    <select name="textura">
                        <option value="">Selecciona</option>
                        <option value="Argilosa">Argilosa</option>
                        <option value="Franca">Franca</option>
                        <option value="Arenosa">Arenosa</option>
                        <option value="Llimosa">Llimosa</option>
                    </select>
                </div>
            </div>

            <button type="submit" class="btn"><i class="fa-solid fa-plus"></i> Afegir parcel·la</button>
        </form>
    </div>

    <?php
    // Afegir nova parcel·la
    if (isset($_POST['nou_parcela'])) {
        $nom         = trim($_POST['nom'] ?? '');
        $superficie  = (float)($_POST['superficie'] ?? 0);
        $coordenades = trim($_POST['coordenades'] ?? '');
        $textura     = trim($_POST['textura'] ?? '');

        if ($nom && $superficie > 0) {
            $stmt = $conn->prepare("
                INSERT INTO Parcel·la 
                (nom, superficie, coordenades, textura)
                VALUES (?, ?, ?, ?)
            ");
            $stmt->bind_param("sdss", $nom, $superficie, $coordenades, $textura);
            $stmt->execute();
            $stmt->close();
            $_SESSION['msg'] = "Parcel·la afegida correctament!";
        } else {
            $_SESSION['err'] = "Nom i superfície són obligatoris";
        }
    }

    // Eliminar parcel·la
    if (isset($_GET['eliminar_parcela'])) {
        $id_parcela = (int)($_GET['id'] ?? 0);
        if ($id_parcela > 0) {
            $conn->query("DELETE FROM Parcel·la WHERE id_parcela = $id_parcela");
            $_SESSION['msg'] = "Parcel·la eliminada correctament!";
        }
        header("Location: index.php?p=parceles");
        exit;
    }
    ?>

    <!-- Llistat de parcel·les amb botó Eliminar -->
    <h3><i class="fa-solid fa-list-ul" style="margin-right:8px; color:var(--text-muted);"></i> Llistat de parcel·les</h3>
    <?php
    $parceles = $conn->query("SELECT id_parcela, nom, superficie, coordenades, textura FROM Parcel·la ORDER BY nom");
    if ($parceles->num_rows > 0):
    ?>
        <div class="table-container">
            <table>
                <tr>
                    <th>ID</th>
                    <th>Nom</th>
                    <th>Superfície (ha)</th>
                    <th>Coordenades</th>
                    <th>Textura</th>
                    <th>Acció</th>
                </tr>
                <?php while ($pr = $parceles->fetch_assoc()): ?>
                    <tr>
                        <td><strong>#<?= $pr['id_parcela'] ?></strong></td>
                        <td><?= htmlspecialchars($pr['nom']) ?></td>
                        <td><span class="badge badge-success"><?= number_format($pr['superficie'], 2) ?> ha</span></td>
                        <td><i class="fa-solid fa-location-dot" style="color:var(--text-muted); margin-right:5px;"></i> <?= htmlspecialchars($pr['coordenades'] ?? '-') ?></td>
                        <td><?= htmlspecialchars($pr['textura'] ?? '-') ?></td>
                        <td>
                            <a href="?p=parceles&eliminar_parcela=1&id=<?= $pr['id_parcela'] ?>"
                               class="btn btn-red btn-icon" onclick="return confirm('Segur que vols eliminaraquesta parcel·la?');" title="Eliminar">
                                <i class="fa-solid fa-trash-can"></i>
                            </a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </table>
        </div>
    <?php else: ?>
        <p style="color:var(--text-muted);"><i class="fa-solid fa-folder-open"></i> Encara no hi ha parcel·les registrades.</p>
    <?php endif; ?>
</div>
