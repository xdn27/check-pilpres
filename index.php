<?php require 'vendor/autoload.php'; ?>

<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Peduli Bangsa</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous">
    <link href="https://cdn.datatables.net/2.0.0/css/dataTables.bootstrap5.min.css" rel="stylesheet" crossorigin="anonymous">
  </head>
  <body>
    <div class="container-fluid p-3 py-md-5">
      <header class="d-flex align-items-center pb-3 mb-5 border-bottom">
        <a href="#" class="d-flex align-items-center text-dark text-decoration-none">
          <span class="fs-4">Potensi Kesalahan Hitung Suara KPU 2024</span>
        </a>
      </header>
      <main>
        

        <table class="table table-striped">
            <thead>
                <tr>
                <th scope="col">Wilayah</th>
                <th scope="col">Suara Sah</th>
                <th scope="col">Suara 01</th>
                <th scope="col">Suara 02</th>
                <th scope="col">Suara 03</th>
                <th scope="col">Potensi Kesalahan</th>
                <th scope="col">Dilihat Pada</th>
                <th scope="col" data-dt-order="disable"></th>
                </tr>
            </thead>
        <tbody>
            <tr>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            </tr>
        </tbody>
        </table>
        
      </main>
      <footer class="pt-5 my-5 text-muted border-top"> Peduli Bangsa </footer>
    </div>

      <script src="https://code.jquery.com/jquery-3.7.1.min.js" integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>
      <!-- <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-C6RzsynM9kWDrMNeT87bh95OGNyZPhcTNXj1NW7RuBCsyN/o0jlpcV8Qyq46cDfL" crossorigin="anonymous"></script> -->
      <script src="https://cdn.datatables.net/2.0.0/js/dataTables.min.js" crossorigin="anonymous"></script>
      <script src="https://cdn.datatables.net/2.0.0/js/dataTables.bootstrap5.min.js" crossorigin="anonymous"></script>

      <script>
        new DataTable('.table', {
                ajax: 'data.php',
                processing: true,
                serverSide: true,
                searchDelay: 1000,
                columnDefs: [{ searchable: false, targets: [1,2,3,4,5,6,7] }]
            });
      </script>
  </body>
</html>