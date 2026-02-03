<?php
// DATABASE CONNECTION
$db = new mysqli("localhost", "shibpurh_school", "@Shibpur1Kantabari9Patnitala66@", "shibpurh_nazipur_high_school");

if ($db->connect_error) {
    die("Connection failed: " . $db->connect_error);
}

mysqli_set_charset($db, "utf8");

// --- PHP LOGIC HANDLERS ---

// 1. DOWNLOAD TABLE AS JSON
if (isset($_GET['download'])) {
    $table = $_GET['download'];
    $data = $db->query("SELECT * FROM `$table`");
    $rows = [];
    while ($row = $data->fetch_assoc()) { $rows[] = $row; }
    header('Content-Type: application/json');
    header('Content-Disposition: attachment; filename="' . $table . '.json"');
    echo json_encode($rows, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    exit;
}

// 2. CREATE TABLE
if (isset($_POST['create_table'])) {
    $name = $db->real_escape_string($_POST['new_table_name']);
    $col_names = $_POST['col_name'];
    $col_types = $_POST['col_type'];
    $col_lens = $_POST['col_len'];
    $col_ais = isset($_POST['col_ai']) ? $_POST['col_ai'] : [];
    
    $definitions = [];
    $primary_key = "";

    for($i=0; $i < count($col_names); $i++){
        if(empty($col_names[$i])) continue;
        
        $col = "`" . $db->real_escape_string($col_names[$i]) . "` " . $col_types[$i];
        
        // Add length if needed (VARCHAR/INT)
        if(!empty($col_lens[$i]) && !in_array($col_types[$i], ['TEXT', 'DATE', 'DATETIME', 'TIMESTAMP'])) {
            $col .= "(" . intval($col_lens[$i]) . ")";
        }

        // Auto Increment check (If checked, it effectively becomes Primary)
        if(in_array($i, $col_ais)) {
            $col .= " AUTO_INCREMENT";
            $primary_key = ", PRIMARY KEY (`" . $db->real_escape_string($col_names[$i]) . "`)";
        } else {
            // Default to NULL for non-AI columns
            $col .= " NULL"; 
        }
        
        $definitions[] = $col;
    }

    $sql = "CREATE TABLE `$name` (" . implode(", ", $definitions) . $primary_key . ") ENGINE=InnoDB DEFAULT CHARSET=utf8;";

    if ($db->query($sql)) {
        $msg = "<div class='alert success'>✅ Table '$name' created successfully!</div>";
    } else {
        $msg = "<div class='alert error'>❌ Error creating table: " . $db->error . "</div>";
    }
}

// 3. ADD COLUMN
if (isset($_POST['add_column'])) {
    $table = $_POST['table'];
    $col = $db->real_escape_string($_POST['col_name']);
    $type = $_POST['col_type'];
    $len = $_POST['col_len'];
    
    $def = "$type";
    if(!empty($len) && !in_array($type, ['TEXT', 'DATE', 'DATETIME'])) {
        $def .= "($len)";
    }
    
    $sql = "ALTER TABLE `$table` ADD `$col` $def";
    
    if ($db->query($sql)) {
        $msg = "<div class='alert success'>✅ Column '$col' added!</div>";
    } else {
        $msg = "<div class='alert error'>❌ Error adding column: " . $db->error . "</div>";
    }
}

// 4. DROP COLUMN
if (isset($_POST['drop_column'])) {
    $table = $_POST['table'];
    $col = $_POST['col_name'];
    if ($db->query("ALTER TABLE `$table` DROP COLUMN `$col`")) {
        $msg = "<div class='alert success'>✅ Column '$col' dropped!</div>";
    } else {
        $msg = "<div class='alert error'>❌ Error dropping column: " . $db->error . "</div>";
    }
}

// 5. RENAME TABLE
if (isset($_POST['rename_table'])) {
    $old = $_POST['old_name'];
    $new = $db->real_escape_string($_POST['new_name']);
    if ($db->query("RENAME TABLE `$old` TO `$new`")) {
        echo "<script>window.location='?table=$new';</script>";
        exit;
    } else {
        $msg = "<div class='alert error'>❌ Error renaming: " . $db->error . "</div>";
    }
}

// 6. DELETE ROW
if (isset($_POST['delete_row'])) {
    $table = $_POST['table'];
    $pk = $_POST['primary_key'];
    $pv = $_POST['primary_value'];
    if ($db->query("DELETE FROM `$table` WHERE `$pk` = '$pv'")) {
        $msg = "<div class='alert success'>✅ Row deleted!</div>";
    } else {
        $msg = "<div class='alert error'>❌ Error: " . $db->error . "</div>";
    }
}

// 7. DELETE TABLE
if (isset($_POST['delete_table'])) {
    $table = $_POST['table'];
    if ($db->query("DROP TABLE `$table`")) {
        echo "<div class='alert success'>✅ Table deleted!</div>";
        header("Refresh:1; url=?");
        exit;
    } else {
        $msg = "<div class='alert error'>❌ Error: " . $db->error . "</div>";
    }
}

// 8. INSERT ROW
if (isset($_POST['insert'])) {
    $table = $_POST['table'];
    $cols = array_keys($_POST['data']);
    $vals = array_values($_POST['data']);
    $cols_sql = "`" . implode("`,`", $cols) . "`";
    $vals_sql = "'" . implode("','", array_map([$db, "real_escape_string"], $vals)) . "'";
    if ($db->query("INSERT INTO `$table` ($cols_sql) VALUES ($vals_sql)")) {
        $msg = "<div class='alert success'>✅ Data inserted!</div>";
    } else {
        $msg = "<div class='alert error'>❌ Error: " . $db->error . "</div>";
    }
}

// 9. UPDATE ROW
if (isset($_POST['update_row'])) {
    $table = $_POST['table'];
    $pk = $_POST['primary_key'];
    $pk_val = $_POST['primary_value'];
    $data = $_POST['data'];
    $set_parts = [];
    foreach ($data as $col => $val) {
        $set_parts[] = "`$col` = '" . $db->real_escape_string($val) . "'";
    }
    $set_sql = implode(", ", $set_parts);
    if ($db->query("UPDATE `$table` SET $set_sql WHERE `$pk` = '$pk_val'")) {
        $msg = "<div class='alert success'>✅ Row updated!</div>";
    } else {
        $msg = "<div class='alert error'>❌ Error: " . $db->error . "</div>";
    }
}

// 10. RAW SQL
if (isset($_POST['query']) && !empty($_POST['query'])) {
    if ($db->query($_POST['query'])) {
        $msg = "<div class='alert success'>✅ Query executed!</div>";
    } else {
        $msg = "<div class='alert error'>❌ Error: " . $db->error . "</div>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Full Database Manager</title>
    <style>
        :root {
            --primary: #2563eb;
            --primary-dark: #1e40af;
            --danger: #dc2626;
            --success: #16a34a;
            --warning: #f59e0b;
            --bg: #f3f4f6;
            --card: #ffffff;
            --text: #1f2937;
            --border: #e5e7eb;
        }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: system-ui, -apple-system, sans-serif; background: var(--bg); color: var(--text); line-height: 1.5; display: flex; flex-direction: column; min-height: 100vh; }
        .container { max-width: 1200px; margin: 0 auto; padding: 20px; width: 100%; flex: 1; }
        
        /* Header */
        header { background: var(--card); border-bottom: 1px solid var(--border); padding: 1.5rem 0; margin-bottom: 2rem; }
        header h1 { font-size: 1.5rem; font-weight: 700; color: var(--primary); text-align: center; }
        header p { text-align: center; color: #6b7280; font-size: 0.875rem; margin-top: 0.5rem; }

        /* General UI */
        .card { background: var(--card); border-radius: 8px; padding: 1.5rem; margin-bottom: 2rem; border: 1px solid var(--border); box-shadow: 0 1px 2px rgba(0,0,0,0.05); }
        h2 { font-size: 1.25rem; font-weight: 600; margin-bottom: 1rem; border-bottom: 2px solid var(--bg); padding-bottom: 0.5rem; color: #111827; }
        h3 { font-size: 1rem; font-weight: 600; margin-bottom: 0.5rem; color: #4b5563; }
        .alert { padding: 1rem; border-radius: 6px; margin-bottom: 1.5rem; font-weight: 500; }
        .alert.success { background: #dcfce7; color: #166534; border: 1px solid #bbf7d0; }
        .alert.error { background: #fee2e2; color: #991b1b; border: 1px solid #fecaca; }

        /* Buttons */
        .btn { display: inline-flex; align-items: center; justify-content: center; padding: 0.5rem 1rem; border-radius: 6px; font-weight: 500; cursor: pointer; transition: 0.2s; border: none; font-size: 0.875rem; text-decoration: none; gap: 0.5rem; }
        .btn-sm { padding: 0.25rem 0.5rem; font-size: 0.75rem; }
        .btn-primary { background: var(--primary); color: white; }
        .btn-primary:hover { background: var(--primary-dark); }
        .btn-danger { background: var(--danger); color: white; }
        .btn-danger:hover { background: #b91c1c; }
        .btn-success { background: var(--success); color: white; }
        .btn-success:hover { background: #15803d; }
        .btn-warning { background: var(--warning); color: white; }
        .btn-outline { background: white; border: 1px solid var(--border); color: var(--text); }
        .btn-outline:hover { background: #f9fafb; border-color: #d1d5db; }

        /* Tables & Lists */
        .tables-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(180px, 1fr)); gap: 1rem; }
        .table-chip { display: block; padding: 0.75rem; background: white; border: 1px solid var(--border); border-radius: 6px; text-decoration: none; color: var(--text); font-weight: 500; text-align: center; transition: 0.1s; }
        .table-chip:hover { border-color: var(--primary); color: var(--primary); background: #eff6ff; transform: translateY(-1px); }
        
        .table-responsive { overflow-x: auto; border-radius: 6px; border: 1px solid var(--border); }
        table { width: 100%; border-collapse: collapse; font-size: 0.875rem; white-space: nowrap; }
        th { background: #f9fafb; text-align: left; padding: 0.75rem 1rem; font-weight: 600; color: #4b5563; border-bottom: 1px solid var(--border); }
        td { padding: 0.75rem 1rem; border-bottom: 1px solid var(--border); }
        tr:last-child td { border-bottom: none; }
        tr:hover { background: #f9fafb; }

        /* Tabs */
        .tabs { display: flex; gap: 1rem; margin-bottom: 1.5rem; border-bottom: 1px solid var(--border); }
        .tab-link { padding: 0.75rem 1rem; text-decoration: none; color: #6b7280; border-bottom: 2px solid transparent; font-weight: 500; }
        .tab-link:hover { color: var(--primary); }
        .tab-link.active { color: var(--primary); border-bottom-color: var(--primary); }

        /* Forms */
        .form-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem; }
        .form-group { margin-bottom: 1rem; }
        .form-group label { display: block; font-size: 0.875rem; font-weight: 500; margin-bottom: 0.3rem; color: #4b5563; }
        input, textarea, select { width: 100%; padding: 0.5rem; border: 1px solid var(--border); border-radius: 6px; font-size: 0.875rem; }
        input:focus, select:focus { outline: none; border-color: var(--primary); box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1); }

        /* Footer */
        footer { background: white; border-top: 1px solid var(--border); padding: 20px; text-align: center; margin-top: auto; }
        footer p { font-size: 0.9rem; color: #6b7280; font-weight: 600; }

        /* Modal */
        .modal { display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1000; align-items: center; justify-content: center; padding: 20px; }
        .modal.active { display: flex; }
        .modal-content { background: white; padding: 2rem; border-radius: 8px; width: 100%; max-width: 600px; max-height: 90vh; overflow-y: auto; box-shadow: 0 20px 25px -5px rgba(0,0,0,0.1); }
        .modal-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; }
        .modal-actions { display: flex; justify-content: flex-end; gap: 1rem; margin-top: 1.5rem; }
    </style>
</head>
<body>

<header>
    <div class="container" style="padding-bottom:0;">
        <h1>🗄️ Full Database Manager</h1>
    </div>
</header>

<div class="container">
    
    <?php if(isset($msg)) echo $msg; ?>

    <div class="card">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:1rem;">
            <h2>📂 Available Tables</h2>
            <button class="btn btn-primary btn-sm" onclick="openModal('createTableModal')">➕ Create New Table</button>
        </div>
        <div class="tables-grid">
            <?php
            $res = $db->query("SHOW TABLES");
            while ($row = $res->fetch_array()) {
                $isActive = (isset($_GET['table']) && $_GET['table'] == $row[0]) ? 'border-color: var(--primary); background: #eff6ff;' : '';
                echo "<a href='?table=" . $row[0] . "' class='table-chip' style='$isActive'>📋 " . $row[0] . "</a>";
            }
            ?>
        </div>
    </div>

    <?php if (isset($_GET['table'])): 
        $table = $_GET['table'];
        $view = isset($_GET['view']) ? $_GET['view'] : 'browse';
    ?>
    
    <div class="card">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:15px; flex-wrap:wrap;">
            <h2>Manager: <span style="color:var(--primary);"><?php echo $table; ?></span></h2>
            <div style="display:flex; gap:10px;">
                <button class="btn btn-warning btn-sm" onclick="openModal('renameTableModal')">✏️ Rename</button>
                <a href="?download=<?php echo $table; ?>" class="btn btn-success btn-sm">⬇️ Export JSON</a>
                <button class="btn btn-danger btn-sm" onclick="openModal('deleteTableModal')">🗑️ Drop Table</button>
            </div>
        </div>

        <div class="tabs">
            <a href="?table=<?php echo $table; ?>&view=browse" class="tab-link <?php echo $view=='browse'?'active':''; ?>">🔍 Browse Data</a>
            <a href="?table=<?php echo $table; ?>&view=structure" class="tab-link <?php echo $view=='structure'?'active':''; ?>">🏗️ Table Structure</a>
        </div>

        <?php if($view == 'browse'): ?>
            <?php
            $structure = $db->query("SHOW COLUMNS FROM `$table`");
            $columns = [];
            $pk = null;
            while($col = $structure->fetch_assoc()){
                $columns[] = $col['Field'];
                if($col['Key'] == 'PRI') $pk = $col['Field'];
            }
            ?>
            <div class="table-responsive">
                <table>
                    <thead>
                        <tr>
                            <?php foreach($columns as $col) echo "<th>$col</th>"; ?>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $data = $db->query("SELECT * FROM `$table` LIMIT 100");
                        if($data->num_rows > 0):
                            while ($row = $data->fetch_assoc()):
                                $rowJson = htmlspecialchars(json_encode($row), ENT_QUOTES, 'UTF-8');
                        ?>
                        <tr>
                            <?php foreach($row as $val) echo "<td>" . htmlspecialchars(substr($val, 0, 50)) . "</td>"; ?>
                            <td style="display:flex; gap:5px;">
                                <?php if($pk): ?>
                                    <button class="btn btn-primary btn-sm" onclick="openEditModal('<?php echo $pk; ?>', '<?php echo $row[$pk]; ?>', <?php echo $rowJson; ?>)">✏️</button>
                                    <form method="post" onsubmit="return confirm('Delete row?');">
                                        <input type="hidden" name="table" value="<?php echo $table; ?>">
                                        <input type="hidden" name="primary_key" value="<?php echo $pk; ?>">
                                        <input type="hidden" name="primary_value" value="<?php echo $row[$pk]; ?>">
                                        <button type="submit" name="delete_row" class="btn btn-danger btn-sm">🗑️</button>
                                    </form>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endwhile; endif; ?>
                    </tbody>
                </table>
            </div>
            
            <div style="margin-top:2rem; padding-top:1rem; border-top:2px dashed var(--border);">
                <h3>➕ Insert New Row</h3>
                <form method="post">
                    <input type="hidden" name="table" value="<?php echo $table; ?>">
                    <div class="form-grid">
                        <?php foreach($columns as $col): ?>
                        <div class="form-group">
                            <label><?php echo $col; ?></label>
                            <input type="text" name="data[<?php echo $col; ?>]">
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <button type="submit" name="insert" class="btn btn-primary">Save Data</button>
                </form>
            </div>

        <?php elseif($view == 'structure'): ?>
            <div class="table-responsive">
                <table>
                    <thead>
                        <tr>
                            <th>Column</th>
                            <th>Type</th>
                            <th>Null</th>
                            <th>Key</th>
                            <th>Default</th>
                            <th>Extra</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $desc = $db->query("DESCRIBE `$table`");
                        while($row = $desc->fetch_assoc()):
                        ?>
                        <tr>
                            <td><strong><?php echo $row['Field']; ?></strong></td>
                            <td><?php echo $row['Type']; ?></td>
                            <td><?php echo $row['Null']; ?></td>
                            <td><?php echo $row['Key']; ?></td>
                            <td><?php echo $row['Default']; ?></td>
                            <td><?php echo $row['Extra']; ?></td>
                            <td>
                                <form method="post" onsubmit="return confirm('Drop column <?php echo $row['Field']; ?>? This will delete all data in this column.');">
                                    <input type="hidden" name="table" value="<?php echo $table; ?>">
                                    <input type="hidden" name="col_name" value="<?php echo $row['Field']; ?>">
                                    <button type="submit" name="drop_column" class="btn btn-danger btn-sm">🗑️ Drop</button>
                                </form>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>

            <div style="margin-top:2rem; padding:1.5rem; background:#f8fafc; border-radius:8px; border:1px solid var(--border);">
                <h3>➕ Add New Column</h3>
                <form method="post" style="display:flex; gap:10px; align-items:flex-end; flex-wrap:wrap;">
                    <input type="hidden" name="table" value="<?php echo $table; ?>">
                    <div style="flex:1; min-width:150px;">
                        <label>Name</label>
                        <input type="text" name="col_name" required placeholder="e.g. email">
                    </div>
                    <div style="flex:1; min-width:150px;">
                        <label>Type</label>
                        <select name="col_type">
                            <option value="INT">INT</option>
                            <option value="VARCHAR">VARCHAR</option>
                            <option value="TEXT">TEXT</option>
                            <option value="DATE">DATE</option>
                            <option value="TIMESTAMP">TIMESTAMP</option>
                        </select>
                    </div>
                    <div style="flex:1; min-width:100px;">
                        <label>Length</label>
                        <input type="number" name="col_len" placeholder="e.g. 255">
                    </div>
                    <button type="submit" name="add_column" class="btn btn-success">Add Column</button>
                </form>
            </div>
        <?php endif; ?>

    </div>
    <?php endif; ?>

    <div class="card">
        <h2>🧪 Execute Raw SQL</h2>
        <form method="post">
            <textarea name="query" rows="4" placeholder="SELECT * FROM users..." style="font-family:monospace; margin-bottom:10px;"></textarea>
            <button type="submit" class="btn btn-primary">▶️ Execute</button>
        </form>
    </div>

</div>

<footer>
    <p>Knight Cyber Security Lab Singapore</p>
</footer>

<div id="createTableModal" class="modal">
    <div class="modal-content">
        <div class="modal-header"><h3>🛠️ Create New Table</h3></div>
        <form method="post">
            <div class="form-group">
                <label>Table Name</label>
                <input type="text" name="new_table_name" required placeholder="e.g. students">
            </div>
            
            <div id="colContainer">
                </div>
            
            <button type="button" class="btn btn-outline btn-sm" onclick="addColRow()" style="margin-bottom:15px; width:100%;">+ Add Another Column</button>

            <div class="modal-actions">
                <button type="button" class="btn" onclick="closeModal('createTableModal')">Cancel</button>
                <button type="submit" name="create_table" class="btn btn-primary">Create Table</button>
            </div>
        </form>
    </div>
</div>

<div id="renameTableModal" class="modal">
    <div class="modal-content">
        <h3>✏️ Rename Table</h3>
        <form method="post">
            <input type="hidden" name="old_name" value="<?php echo $table ?? ''; ?>">
            <div class="form-group" style="margin-top:15px;">
                <label>New Name</label>
                <input type="text" name="new_name" required value="<?php echo $table ?? ''; ?>">
            </div>
            <div class="modal-actions">
                <button type="button" class="btn" onclick="closeModal('renameTableModal')">Cancel</button>
                <button type="submit" name="rename_table" class="btn btn-primary">Rename</button>
            </div>
        </form>
    </div>
</div>

<div id="deleteTableModal" class="modal">
    <div class="modal-content">
        <h3>⚠️ Confirm Drop Table</h3>
        <p>Are you sure you want to delete table <strong><?php echo $table ?? ''; ?></strong>? This action is permanent!</p>
        <form method="post" class="modal-actions">
            <input type="hidden" name="table" value="<?php echo $table ?? ''; ?>">
            <button type="button" class="btn" onclick="closeModal('deleteTableModal')">Cancel</button>
            <button type="submit" name="delete_table" class="btn btn-danger">Yes, Delete</button>
        </form>
    </div>
</div>

<div id="editRowModal" class="modal">
    <div class="modal-content">
        <div class="modal-header"><h3>✏️ Edit Row Data</h3></div>
        <form method="post">
            <input type="hidden" name="table" value="<?php echo $table ?? ''; ?>">
            <input type="hidden" name="primary_key" id="editPkName">
            <input type="hidden" name="primary_value" id="editPkValue">
            <div id="editFieldsContainer"></div>
            <div class="modal-actions">
                <button type="button" class="btn" onclick="closeModal('editRowModal')">Cancel</button>
                <button type="submit" name="update_row" class="btn btn-primary">Save Changes</button>
            </div>
        </form>
    </div>
</div>

<script>
// --- UTILS ---
function openModal(id) { document.getElementById(id).classList.add('active'); }
function closeModal(id) { document.getElementById(id).classList.remove('active'); }
window.onclick = function(e) { if(e.target.classList.contains('modal')) e.target.classList.remove('active'); }

// --- EDIT ROW LOGIC ---
function openEditModal(pkName, pkValue, rowData) {
    document.getElementById('editPkName').value = pkName;
    document.getElementById('editPkValue').value = pkValue;
    const container = document.getElementById('editFieldsContainer');
    container.innerHTML = '';
    for (const [key, value] of Object.entries(rowData)) {
        const div = document.createElement('div');
        div.className = 'form-group';
        div.innerHTML = `<label>${key}</label><input type="text" name="data[${key}]" value="${value !== null ? value : ''}">`;
        container.appendChild(div);
    }
    openModal('editRowModal');
}

// --- CREATE TABLE LOGIC (Dynamic Rows) ---
let colCount = 0;
function addColRow() {
    const container = document.getElementById('colContainer');
    const row = document.createElement('div');
    row.style.cssText = "display:flex; gap:5px; margin-bottom:10px; border-bottom:1px solid #eee; padding-bottom:10px;";
    
    row.innerHTML = `
        <div style="flex:2;"><input type="text" name="col_name[]" placeholder="Column Name" required></div>
        <div style="flex:1;">
            <select name="col_type[]">
                <option value="INT">INT</option>
                <option value="VARCHAR">VARCHAR</option>
                <option value="TEXT">TEXT</option>
                <option value="DATE">DATE</option>
            </select>
        </div>
        <div style="flex:1;"><input type="number" name="col_len[]" placeholder="Len" title="Length"></div>
        <div style="flex:0.5; display:flex; align-items:center; justify-content:center;">
            <label style="font-size:0.7em;">AI<br><input type="checkbox" name="col_ai[]" value="${colCount}"></label>
        </div>
    `;
    container.appendChild(row);
    colCount++;
}

// Add first row on load
document.addEventListener('DOMContentLoaded', () => {
    if(document.getElementById('colContainer')) addColRow();
});
</script>

</body>
</html>
