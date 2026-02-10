<h2>Personal</h2>

<!-- Treballadors -->
<div class="form-section">
    <h3>Afegir treballador</h3>
    <form method="post">
        <input type="hidden" name="p" value="personal">
        <input type="hidden" name="nou_treballador" value="1">
        NIF: <input type="text" name="nif" required><br><br>
        Nom: <input type="text" name="nom" required><br><br>
        Cognoms: <input type="text" name="cognoms" required><br><br>
        Telèfon: <input type="text" name="telefon"><br><br>
        <button type="submit" class="btn">Afegir</button>
    </form>
</div>

<?php if ($treballadors_list->num_rows > 0): ?>
    <table>
        <tr><th>NIF</th><th>Nom complet</th><th>Telèfon</th><th>Acció</th></tr>
        <?php $treballadors_list->data_seek(0); while($t = $treballadors_list->fetch_assoc()): ?>
            <tr>
                <td><?= htmlspecialchars($t['nif']) ?></td>
                <td><?= htmlspecialchars($t['nom'] . ' ' . $t['cognoms']) ?></td>
                <td><?= htmlspecialchars($t['telefon'] ?? '-') ?></td>
                <td><a href="?eliminar=1&tipus=treballador&id=<?= $t['id_treballador'] ?>&p=personal" class="btn btn-red" onclick="return confirm('Segur?');">Eliminar</a></td>
            </tr>
        <?php endwhile; ?>
    </table>
<?php else: ?>
    <p>Encara no hi ha treballadors.</p>
<?php endif; ?>

<!-- Absències -->
<div style="margin-top:60px;">
    <h2>Absències</h2>
    <div class="form-section">
        <form method="post">
            <input type="hidden" name="p" value="personal">
            <input type="hidden" name="nova_absencia" value="1">
            Treballador:
            <select name="id_treballador" required>
                <option value="">Selecciona</option>
                <?php $treballadors_list->data_seek(0); while($t = $treballadors_list->fetch_assoc()): ?>
                    <option value="<?= $t['id_treballador'] ?>"><?= htmlspecialchars($t['nif'] . ' - ' . $t['nom'] . ' ' . $t['cognoms']) ?></option>
                <?php endwhile; ?>
            </select><br><br>
            Tipus:
            <select name="tipus" required>
                <option value="">Selecciona</option>
                <option value="Baixa mèdica">Baixa mèdica</option>
                <option value="Vacances">Vacances</option>
                <option value="Assumptes personals">Assumptes personals</option>
                <option value="Altres">Altres</option>
            </select><br><br>
            Data inici: <input type="date" name="data_inici" required><br><br>
            Data fi: <input type="date" name="data_fi"><br><br>
            <label><input type="checkbox" name="aprovada" value="1"> Ja aprovada</label><br><br>
            <button type="submit" class="btn">Afegir absència</button>
        </form>
    </div>

    <?php if ($absencies_list->num_rows > 0): ?>
        <table>
            <tr><th>Treballador</th><th>Tipus</th><th>Data inici</th><th>Data fi</th><th>Estat</th><th>Accions</th></tr>
            <?php while($a = $absencies_list->fetch_assoc()): ?>
                <tr>
                    <td><?= htmlspecialchars($a['nom'] . ' ' . $a['cognoms']) ?></td>
                    <td><?= htmlspecialchars($a['tipus']) ?></td>
                    <td><?= htmlspecialchars($a['data_inici']) ?></td>
                    <td><?= $a['data_fi'] ?: '-' ?></td>
                    <td>
                        <?php if ($a['aprovada']): ?>
                            <span style="color:#16a34a;">Aprovada</span>
                        <?php else: ?>
                            <a href="?aprovar_absencia=1&id=<?= $a['id_absencia'] ?>&p=personal" class="btn btn-aprovar">Aprovar</a>
                        <?php endif; ?>
                    </td>
                    <td><a href="?eliminar=1&tipus=absencia&id=<?= $a['id_absencia'] ?>&p=personal" class="btn btn-red" onclick="return confirm('Segur?');">Eliminar</a></td>
                </tr>
            <?php endwhile; ?>
        </table>
    <?php else: ?>
        <p>No hi ha absències registrades.</p>
    <?php endif; ?>
</div>

<!-- Contractes -->
<div style="margin-top:60px;">
    <h2>Contractes</h2>
    <div class="form-section">
        <h3>Afegir / Actualitzar contracte</h3>
        <form method="post">
            <input type="hidden" name="p" value="personal">
            <input type="hidden" name="nou_contracte" value="1">
            Treballador:
            <select name="id_treballador" required>
                <option value="">Selecciona</option>
                <?php $treballadors_list->data_seek(0); while($t = $treballadors_list->fetch_assoc()): ?>
                    <option value="<?= $t['id_treballador'] ?>"><?= htmlspecialchars($t['nif'] . ' - ' . $t['nom'] . ' ' . $t['cognoms']) ?></option>
                <?php endwhile; $treballadors_list->data_seek(0); ?>
            </select><br><br>
            Categoria professional: <input type="text" name="categoria_professional" required><br><br>
            Salari brut anual (€): <input type="number" name="salari_brut_anual" step="0.01" required><br><br>
            <button type="submit" class="btn">Guardar contracte</button>
        </form>
    </div>

    <?php if ($contractes_list->num_rows > 0): ?>
        <table>
            <tr><th>Treballador</th><th>Categoria</th><th>Salari (€/any)</th><th>Acció</th></tr>
            <?php while($c = $contractes_list->fetch_assoc()): ?>
                <tr>
                    <td><?= htmlspecialchars($c['nif'] . ' - ' . $c['nom'] . ' ' . $c['cognoms']) ?></td>
                    <td><?= htmlspecialchars($c['categoria_professional']) ?></td>
                    <td><?= number_format($c['salari_brut_anual'], 2) ?></td>
                    <td><a href="?eliminar=1&tipus=contracte&id=<?= $c['id_contracte'] ?>&p=personal" class="btn btn-red" onclick="return confirm('Segur?');">Eliminar</a></td>
                </tr>
            <?php endwhile; ?>
        </table>
    <?php else: ?>
        <p>Encara no hi ha contractes.</p>
    <?php endif; ?>
</div>

<!-- Tasques -->
<div style="margin-top:60px;">
    <h2>Tasques</h2>
    <div class="form-section">
        <form method="post">
            <input type="hidden" name="p" value="personal">
            <input type="hidden" name="nova_tasca" value="1">
            Nom: <input type="text" name="nom" required><br><br>
            Descripció: <textarea name="descripcio"></textarea><br><br>
            ID Parcel·la: <input type="number" name="id_parcela" required><br><br>
            Sector (opcional):
            <select name="id_sector">
                <option value="">Sense sector</option>
                <?php while($s = $sectors_tasques->fetch_assoc()): ?>
                    <option value="<?= $s['id_sector'] ?>"><?= htmlspecialchars($s['nom']) ?></option>
                <?php endwhile; ?>
            </select><br><br>
            Hores estimades: <input type="number" name="hores_estimades" step="0.01" required><br><br>
            <button type="submit" class="btn">Afegir tasca</button>
        </form>
    </div>

    <?php if ($tasques_list->num_rows > 0): ?>
        <table>
            <tr><th>ID</th><th>Nom</th><th>Descripció</th><th>ID Sector</th><th>ID Parcel·la</th><th>Hores</th><th>Estat</th><th>Accions</th></tr>
            <?php while($t = $tasques_list->fetch_assoc()): ?>
                <tr class="<?= $t['finalitzada'] ? 'finalitzada' : '' ?>">
                    <td><?= $t['id_tasca'] ?></td>
                    <td><?= htmlspecialchars($t['nom']) ?></td>
                    <td><?= htmlspecialchars($t['descripcio'] ?? '-') ?></td>
                    <td><?= $t['id_sector'] ?: '-' ?></td>
                    <td><?= $t['id_parcela'] ?: '-' ?></td>
                    <td><?= $t['hores_estimades'] ?: '0' ?></td>
                    <td><?= $t['finalitzada'] ? 'Finalitzada' : 'Pendent' ?></td>
                    <td>
                        <?php if (!$t['finalitzada']): ?>
                            <a href="?finalitzar=1&id=<?= $t['id_tasca'] ?>&p=personal" class="btn btn-aprovar">Finalitzar</a>
                        <?php endif; ?>
                        <a href="?eliminar=1&tipus=tasca&id=<?= $t['id_tasca'] ?>&p=personal" class="btn btn-red" onclick="return confirm('Segur?');">Eliminar</a>
                    </td>
                </tr>
            <?php endwhile; ?>
        </table>
    <?php else: ?>
        <p>Encara no hi ha tasques.</p>
    <?php endif; ?>
</div>

<!-- Assignacions -->
<div style="margin-top:60px;">
    <h2>Assignacions</h2>
    <div class="form-section">
        <form method="post">
            <input type="hidden" name="p" value="personal">
            <input type="hidden" name="nou_assignacio" value="1">
            Tasca:
            <select name="id_tasca" required>
                <option value="">Selecciona tasca</option>
                <?php $tasques_list->data_seek(0); while($t = $tasques_list->fetch_assoc()): ?>
                    <option value="<?= $t['id_tasca'] ?>"><?= htmlspecialchars($t['nom']) ?></option>
                <?php endwhile; $tasques_list->data_seek(0); ?>
            </select><br><br>
            Treballador:
            <select name="id_treballador" required>
                <option value="">Selecciona</option>
                <?php $treballadors_list->data_seek(0); while($t = $treballadors_list->fetch_assoc()): ?>
                    <option value="<?= $t['id_treballador'] ?>"><?= htmlspecialchars($t['nif'] . ' - ' . $t['nom'] . ' ' . $t['cognoms']) ?></option>
                <?php endwhile; ?>
            </select><br><br>
            <label><input type="checkbox" name="es_cap_equip" value="1"> És cap d'equip</label><br><br>
            <button type="submit" class="btn">Afegir assignació</button>
        </form>
    </div>

    <?php if ($assignacions_list->num_rows > 0): ?>
        <table>
            <tr><th>ID</th><th>Tasca</th><th>Treballador</th><th>Cap d'equip?</th><th>Acció</th></tr>
            <?php while($a = $assignacions_list->fetch_assoc()): ?>
                <tr>
                    <td><?= $a['id_assignacio'] ?></td>
                    <td><?= $a['id_tasca'] ?></td>
                    <td><?= htmlspecialchars($a['nif'] . ' - ' . $a['nom'] . ' ' . $a['cognoms']) ?></td>
                    <td><?= $a['es_cap_equip'] ? 'Sí' : 'No' ?></td>
                    <td><a href="?eliminar=1&tipus=assignacio&id=<?= $a['id_assignacio'] ?>&p=personal" class="btn btn-red" onclick="return confirm('Segur?');">Eliminar</a></td>
                </tr>
            <?php endwhile; ?>
        </table>
    <?php else: ?>
        <p>Encara no hi ha assignacions.</p>
    <?php endif; ?>
</div>
