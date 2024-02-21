<html>
<head>
<style>

.dark-blue-bg {
    background-color: #172260;
    color: white;
}

  table {
	border-collapse: collapse;
	width: 100%;
	margin-top: 30px;
	margin-bottom: 30px;
  }
  
  table, th, td {
	border: 1px solid black;
  }
  
  th, td {
	padding: 8px;
  }
  
  th {
	background-color: #f2f2f2; /* Header row background color */
  }
  
  tr:nth-child(even) {
	background-color: #f9f9f9; /* Even row background color */
  }
  
  tr:nth-child(odd) 
  {
	background-color: #ffffff; /* Odd row background color */
  }

#stickyFooter {
  position: fixed;
  bottom: 0;
  left: 0;
  width: 100%;
  background-color: #f1f1f1;
  padding: 20px;
}

</style>
    <script src="/js/htmx.min.js"></script>
    <link rel="apple-touch-icon"                  sizes="57x57"    href="/images/favicon/apple-icon-57x57.png">
    <link rel="apple-touch-icon"                  sizes="60x60"    href="/images/favicon/apple-icon-60x60.png">
    <link rel="apple-touch-icon"                  sizes="72x72"    href="/images/favicon/apple-icon-72x72.png">
    <link rel="apple-touch-icon"                  sizes="76x76"    href="/images/favicon/apple-icon-76x76.png">
    <link rel="apple-touch-icon"                  sizes="114x114"  href="/images/favicon/apple-icon-114x114.png">
    <link rel="apple-touch-icon"                  sizes="120x120"  href="/images/favicon/apple-icon-120x120.png">
    <link rel="apple-touch-icon"                  sizes="144x144"  href="/images/favicon/apple-icon-144x144.png">
    <link rel="apple-touch-icon"                  sizes="152x152"  href="/images/favicon/apple-icon-152x152.png">
    <link rel="apple-touch-icon"                  sizes="180x180"  href="/images/favicon/apple-icon-180x180.png">
    <link rel="icon"             type="image/png" sizes="192x192"  href="/images/favicon/android-icon-192x192.png">
    <link rel="icon"             type="image/png" sizes="32x32"    href="/images/favicon/favicon-32x32.png">
    <link rel="icon"             type="image/png" sizes="96x96"    href="/images/favicon/favicon-96x96.png">
    <link rel="icon"             type="image/png" sizes="16x16"    href="/images/favicon/favicon-16x16.png">
    <link rel="manifest"                                           href="/images/favicon/manifest.json">
    <meta name="msapplication-TileColor"                           content="#ffffff">
    <meta name="msapplication-TileImage"                           content="/images/favicon/ms-icon-144x144.png">
    <meta name="theme-color"                                       content="#ffffff">
</head>


<?php  $isDeveloper = (DataAccessManager::get("session")->getCurrentUser() && DataAccessManager::get("persona")->isDeveloper(DataAccessManager::get("session")->getCurrentUser())); ?>

<?php  if ($isDeveloper): ?>
<script>
    <?php require_once $main_dir."/src_data_access/JS/gtk_testing_init.js"; ?>
</script>

<?php endif; ?>

<body>

<div>
    <?php if ($isDeveloper): ?>
    <?php
        $values = DataAccessManager::get("DataAccessManager")->linksForUser(DataAccessManager::get("session")->getCurrentUser()); 
        foreach ($values as $value)
        {
            $url = $value["label"];
            $url = $value["url"];
            echo "<a href='$url'>$url</a><br>";
        }  
    ?>
    <?php endif; ?>
</div>



<h1>App</h1>
<div id="links">
    
    <a href="/index.php">Inicio</a>

    <br><b>HIT - Haina</b>
    <br><a href="/hit/reservaRetiroContenedor.php">Reserva Retiro Contenedor HIT</a>
    <br><a href="/hit/verificiarEstadoContenedor.php">Verificar Estado Contenedor HIT</a>
    
    <!-- <br><b>Caucedo</b> -->
    <!-- <br><a href="/reservaRetiroContenedor.php">Reserva Retiro Contenedor HIT</a> -->
    <!-- <br><a href="/checkHitContainerStatus.php">Verificar Estado Contenedor HIT</a> -->
    
    <!-- ChassisPlatformBookingRequest -->
    <!-- ChassisProviderBookingRequest -->
    <!-- ChassisConsumerBookingRequest -->

    <?php if (DataAccessManager::get("session")->getCurrentUser()): ?>

    <?php if     (DataAccessManager::get('persona')->isPlatformManager(DataAccessManager::get("session")->getCurrentUser())): ?>    
        <br/><a href="/platform/BookingRequest/all.php">Solicitudes de Chassis</a>    
    <?php elseif (DataAccessManager::get('persona')->isChassisProvider(DataAccessManager::get("session")->getCurrentUser())): ?>
        <br/><a href="/provider/BookingRequest/all">Solicitudes de Chassis</a>    
    <?php elseif (DataAccessManager::get('persona')->isChassisConsumer(DataAccessManager::get("session")->getCurrentUser())): ?>    
        <br/><a href="/consumer/BookingRequest/all">Solicitudes de Chassis</a>    
    <?php endif; ?>
    

        <br/><a href="/logout.php">Logout</a>
    <?php else: ?>
        <br/><a href="/login.php">Login</a>
    <?php endif; ?>
</div>
