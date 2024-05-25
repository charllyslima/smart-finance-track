<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>API Documentation</title>
    <link rel="stylesheet" type="text/css" href="{{ asset('swagger-ui/swagger-ui.css') }}">
    <style>
        body {
            margin: 0;
            padding: 0;
        }
    </style>
</head>
<body>
    <div id="swagger-ui"></div>
    <script src="{{ asset('swagger-ui/swagger-ui-bundle.js') }}"></script>
    <script src="{{ asset('swagger-ui/swagger-ui-standalone-preset.js') }}"></script>
    <script>
        window.onload = function() {
            const ui = SwaggerUIBundle({
                url: "{{ url('/swagger/swagger.yaml') }}",
                dom_id: '#swagger-ui',
                presets: [
                    SwaggerUIBundle.presets.apis,
                    SwaggerUIStandalonePreset
                ],
                layout: "StandaloneLayout",
                validatorUrl: null // Desativar o validador
            });
            window.ui = ui;
        }
    </script>
</body>
</html>
