<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.5.3/dist/css/bootstrap.min.css" integrity="sha384-TX8t27EcRE3e/ihU7zmQxVncDAy5uIKz4rEkgIXeMed4M0jlfIDPvg6uqKI2xXr2" crossorigin="anonymous">
    <link rel="preconnect" href="https://fonts.gstatic.com">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:ital,wght@1,300&display=swap" rel="stylesheet">
    <title>RestoPass - Nouveau Mot de Passe</title>
    <style>
        body{
            background-image: url('images/background.jpg')
        }

        .card {
            width: 600px;
            margin: 0 auto;
            margin-top: 100px;
            float: none;
            margin-bottom: 10px;
            font-family: 'Roboto', sans-serif;
        }

    </style>
</head>
<body>
    <div class="container">
        <div class="card" style="width: 600px">
            <div class="card-header">
                <h1 class="text-center">RestoPass</h1>
            </div>
            <div class="card-body">
                <form  method="POST" action="{{ route('password.update') }}">
                  <fieldset>
                    <legend>Réinitialisation de mot de passe</legend>
                    @csrf
                    <input type="hidden" name="token" value="{{ $token }}">

                    @if($errors->any())
                        <div class="alert alert-danger">Vérifier les informations saisis</div>
                    @endif

                    @isset($status)
                        <h3 class="alert alert-danger"> {{ $status }}</h3>
                    @endisset

                    <div class="form-group">
                        <label for="nom">Adresse email</label>
                    <input type="email" class="form-control {{ $errors->has('email') ? "is-invalid" : "" }}" id="email" aria-describedby="email" name="email" value="{{ old('email') }}" required>
                        <div id="email" class="invalid-feedback">
                            Adresse email invalide !
                        </div>
                    </div>


                    <div class="form-group">
                      <label for="email">Nouveau mot de passe</label>
                      <input type="password" class="form-control" name="password" required >
                    </div>

                    <div class="form-group">
                      <label for="email">Confirmer le mot de passe</label>
                      <input type="password" class="form-control" name="password_confirmation" required >
                    </div>

                    <div class="form-group">
                      <button class="btn btn-info btn-block" type="submit">Valider</button>
                    </div>
                  </fieldset>
                </form>
            </div>
        </div>
    </div>

</body>
</html>


