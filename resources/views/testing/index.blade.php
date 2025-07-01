<!doctype html>
<html lang="en">
  <head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css" integrity="sha384-xOolHFLEh07PJGoPkLv1IbcEPTNtaed2xpHsD9ESMhqIYd0nLMwNLD69Npy4HI+N" crossorigin="anonymous">

    <title>Testing S3 Bucket</title>
  </head>
  <body>
    <div class="container mt-5">
      <h1>Upload Picture</h1>
      <form action="{{ route('testing.upload') }}" method="POST" enctype="multipart/form-data">
        @csrf
        <div class="form-group">
          <label for="avatar">Choose a picture</label>
          <input type="file" class="form-control-file" id="avatar" name="avatar" required />
        </div>
        <button type="submit" class="btn btn-primary">Upload</button>
      </form>

      @if(session('message'))
        <div class="alert alert-success mt-3">
          {{ session('message') }}
        </div>
      @endif
      @if($errors->any())
        <div class="alert alert-danger mt-3">
          <ul>
            @foreach($errors->all() as $error)
              <li>{{ $error }}</li>
            @endforeach
          </ul>
        </div>
      @endif
    </div>

    <script src="https://cdn.jsdelivr.net/npm/jquery@3.5.1/dist/jquery.slim.min.js" integrity="sha384-DfXdz2htPH0lsSSs5nCTpuj/zy4C+OGpamoFVy38MVBnE+IbbVYUew+OrCXaRkfj" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-Fy6S3B9q64WdZWQUiU+q4/2Lc9npb8tCaSX9FK7E8HnRr0Jz8D6OP9dO5Vg3Q9ct" crossorigin="anonymous"></script>
  </body>
</html>
