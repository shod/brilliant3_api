<!DOCTYPE html>
<html>

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Map for trace</title>

  @vite('resources/css/app.css')
</head>

<body>  
  <script>
    const base_url = "{{ $base_url }}"
    const map_image_path = "{{ $map_image_path }}"
    const device_image_path = "{{ $device_image_path }}"    
    const deviceId = "{{$device_id}}"
  </script>
  <data_ptp data="{{ $data }}"></data_ptp>
  <div id="app"></div>
    @vite('resources/js/app.js')

    <style>
      * {
        padding: 0;
        margin: 0;
      }
      #app {        
        text-align: left;  
        /*background-image: url("http://br3api.loc/images/map_main.png");*/        

        /* Center and scale the image nicely */
        background-position: center;
        background-repeat: no-repeat;
        background-size: cover;
      }
      </style> 
</body>
</html>