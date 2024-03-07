<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body>
    <form action="test2" method="POST" enctype="multipart/form-data">
    @csrf
    <input type="file" name="excel_file">
    <button type="submit">単純アップロード</button>
    </form>


</body>
</html>