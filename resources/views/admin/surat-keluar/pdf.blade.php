<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">

    <style>
        @page {
            size: A4 portrait;
            margin: 12mm 20mm 12mm 25mm;
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            padding: 0;
            color: #000;
            font-family: "Times New Roman", Times, serif;
            font-size: 12pt;
            line-height: 1.4;
        }

        p {
            margin-top: 0;
            margin-bottom: 10px;
        }

        table {
            border-collapse: collapse;
        }

        td {
            vertical-align: top;
        }

        img {
            max-width: 100%;
        }
    </style>
</head>

<body>

@if(!empty($watermark))
<div style="position: fixed; top: 380px; left: 0; width: 100%; text-align: center; font-size: 48pt; font-weight: bold; color: #d32f2f; opacity: 0.3;">DRAFT<br><span style="font-size: 20pt;">BELUM DISAHKAN</span></div>
@endif

{!! $isiSurat !!}

</body>
</html>
