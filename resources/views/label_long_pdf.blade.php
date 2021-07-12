<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Label Long</title>
    <link rel="shortcut icon" type="image/x-icon" href="storage/images/dem.ico" />
    <style>

        @page {
            margin: 18px;
        }
        
        body {
            margin: 18px;
            font-family: sans-serif;
            font-size: 12px;
        }

        .border {
            border-collapse: collapse;
            border: 1px solid #333333;
        }

        .border-white {
            border-collapse: collapse;
            border: 1px solid #FFFFFF;
        }

        .outline {
            border: 2px solid #333333;
        }

        .bg-color {
            background-color: #d9d9d9;
        }

        .text-center {
            text-align: center;
        }

        .font-ok {
            font-weight: bold;
            font-size: 32px;
            color: #333333;
        }

        .valign-top {
            vertical-align: top;
        }

        .valign-middle {
            vertical-align: middle;
        }

        .padding-ok {
            padding: 10px 10px;
        }

        .padding-long {
            padding: 24px 290px 24px 290px;
        }

        .martop {
            margin-top: 28px;
        }

        .marright {
            margin-right: 10px
        }
    </style>
</head>
<body>
    <table border="4" class="border outline">
        <tr>
            <td class="padding-long text-center">
                <img src="data:image/png;base64,{!! base64_encode(QrCode::format('png')->generate("202008101113Y")); !!}"
                width="120px">
            </td>
        </tr>
        <tr>
            <td class="padding-long text-center">
                202008101113Y
            </td>
        </tr>
    </table>
    <table border="4" class="border outline martop">
        <tr>
            <td class="padding-long text-center">
                <img src="data:image/png;base64,{!! base64_encode(QrCode::format('png')->generate("202008101113Y")); !!}"
                width="120px">
            </td>
        </tr>
        <tr>
            <td class="padding-long text-center">
                202008101113Y
            </td>
        </tr>
    </table>
    <table border="4" class="border outline martop">
        <tr>
            <td class="padding-long text-center">
                <img src="data:image/png;base64,{!! base64_encode(QrCode::format('png')->generate("202008101113Y")); !!}"
                width="120px">
            </td>
        </tr>
        <tr>
            <td class="padding-long text-center">
                202008101113Y
            </td>
        </tr>
    </table>
    <table border="4" class="border outline martop">
        <tr>
            <td class="padding-long text-center">
                <img src="data:image/png;base64,{!! base64_encode(QrCode::format('png')->generate("202008101113Y")); !!}"
                width="120px">
            </td>
        </tr>
        <tr>
            <td class="padding-long text-center">
                202008101113Y
            </td>
        </tr>
    </table>
</body>
</html>