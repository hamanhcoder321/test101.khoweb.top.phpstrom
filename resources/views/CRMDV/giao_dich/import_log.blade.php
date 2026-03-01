<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Preview import giao dịch</title>

    <style>
        :root {
            --bg-page: #f4f6f9;
            --bg-card: #ffffff;
            --border: #e5e7eb;
            --text: #1f2937;
            --muted: #6b7280;

            --ok-bg: #ecfdf5;
            --ok-text: #047857;

            --err-bg: #fef2f2;
            --err-text: #b91c1c;

            --primary: #2563eb;
            --primary-hover: #1d4ed8;
            --shadow: 0 8px 24px rgba(0,0,0,.08);
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            padding: 24px;
            font-family: Inter, "Segoe UI", Roboto, Arial, sans-serif;
            font-size: 13px;
            color: var(--text);
            background: var(--bg-page);
        }

        h3 {
            margin: 0 0 16px;
            font-size: 20px;
            font-weight: 600;
        }

        table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            background: var(--bg-card);
            border-radius: 10px;
            overflow: hidden;
            box-shadow: var(--shadow);
        }

        thead th {
            background: #f9fafb;
            padding: 12px 10px;
            font-weight: 600;
            text-align: center;
            border-bottom: 1px solid var(--border);
            white-space: nowrap;
        }

        tbody td {
            padding: 10px;
            border-bottom: 1px solid var(--border);
            vertical-align: middle;
        }

        tbody tr:last-child td {
            border-bottom: none;
        }

        tbody tr:hover {
            background: #f9fafb;
        }

        tr.ok {
            background: linear-gradient(90deg, rgba(16,185,129,.08), transparent);
        }

        tr.error {
            background: linear-gradient(90deg, rgba(239,68,68,.08), transparent);
        }

        tr.ok td:nth-last-child(2) {
            color: var(--ok-text);
            font-weight: 600;
        }

        tr.error td:nth-last-child(2),
        tr.error td:last-child {
            color: var(--err-text);
            font-weight: 600;
        }

        td:nth-child(1),
        td:nth-last-child(2) {
            text-align: center;
            font-weight: 500;
        }

        td:nth-child(5),
        td:nth-child(6) {
            text-align: right;
            font-variant-numeric: tabular-nums;
        }

        a {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 9px 14px;
            border-radius: 6px;
            border: 1px solid var(--border);
            background: #fff;
            color: var(--text);
            text-decoration: none;
            transition: .2s;
        }

        a:hover {
            background: #f3f4f6;
        }

        button {
            padding: 9px 16px;
            border-radius: 6px;
            border: none;
            cursor: pointer;
            background: var(--primary);
            color: #fff;
            font-weight: 500;
            box-shadow: 0 4px 10px rgba(37,99,235,.25);
            transition: .2s;
        }

        button:hover {
            background: var(--primary-hover);
        }

        form {
            display: inline-block;
            margin-left: 8px;
        }

        tbody td[colspan] {
            padding: 20px;
            text-align: center;
            color: var(--muted);
            font-style: italic;
        }
    </style>
</head>
<body>

<h3>PREVIEW IMPORT</h3>

<table>
    <thead>
    <tr>
        <th>Dòng</th>
        <th>Ngày giao dịch</th>
        <th>Số GD</th>
        <th>Nội dung</th>
        <th>Số tiền</th>
        <th>Tên đối ứng</th>
        <th>TK đối ứng</th>
        <th>Trạng thái</th>
        <th>Ghi chú</th>
    </tr>
    </thead>
    <tbody>
    <?php if (!empty($rows)): ?>
        <?php foreach ($rows as $r): ?>
    <tr class="<?php echo $r['ok'] ? 'ok' : 'error'; ?>">
        <td><?php echo $r['row']; ?></td>
        <td><?php echo $r['data']['transaction_date'] ?? ''; ?></td>
        <td><?php echo $r['data']['transaction_number'] ?? ''; ?></td>
        <td><?php echo $r['data']['transaction_content'] ?? ''; ?></td>
        <td><?php echo number_format($r['data']['amount'] ?? 0); ?></td>
        <td><?php echo $r['data']['tk_doi_ung_ten'] ?? ''; ?></td>
        <td><?php echo $r['data']['tk_doi_ung'] ?? ''; ?></td>
        <td><?php echo $r['ok'] ? 'OK' : 'LỖI'; ?></td>
        <td><?php echo htmlspecialchars($r['msg']); ?></td>
    </tr>
    <?php endforeach; ?>
    <?php else: ?>
    <tr>
        <td colspan="9">Không có dữ liệu</td>
    </tr>
    <?php endif; ?>
    </tbody>
</table>

<br>

<a href="/admin/giao_dich/import-excel">⬅ Back</a>

<form method="POST" action="/admin/giao_dich/import-commit">
    <input type="hidden" name="_token" value="<?php echo csrf_token(); ?>">
    <button type="submit">▶ Tiếp tục lưu</button>
</form>

</body>
</html>
