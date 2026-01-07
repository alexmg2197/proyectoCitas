<?php 
require_once 'src/auth.php'; 
require_once 'src/Database.php';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Booking Pro - Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body class="bg-gray-100 flex ">
    <div class="flex-1 flex flex-col min-h-screen">
        
        <header class="bg-white shadow-sm h-16 w-full flex items-center justify-between px-4 md:px-8 shrink-0 z-10">
            <button onclick="toggleSidebar()" class="text-gray-600 md:hidden p-2 focus:outline-none">
                <i class="fas fa-bars text-xl"></i>
            </button>   
            
            <div class="flex items-center gap-4">
                <span class="text-gray-500 text-sm md:text-base hidden sm:block">
                    <i class="far fa-calendar-alt mr-2"></i><?php echo date('d/m/Y'); ?>
                </span>
                <span id="reloj" class="text-gray-500 md:text-base text-sm font-mono">00:00:00</span>
            </div>
            
            <div class="flex items-center gap-3 md:gap-6">
                <div class="text-right xs:block">
                    <p class="text-xs md:text-sm font-bold text-gray-800 line-clamp-1 uppercase">
                        <?php echo htmlspecialchars($_SESSION['usuario_nombre']); ?>
                    </p>
                    <p class="text-[10px] md:text-xs text-green-500 italic font-semibold">En línea</p>
                </div>
                
                <a href="logout.php" class="bg-red-50 text-red-600 p-2 md:px-4 md:py-2 rounded-lg text-sm font-semibold hover:bg-red-100 transition flex items-center">
                    <i class="fas fa-sign-out-alt md:mr-2"></i>
                    <span class="hidden md:inline">Cerrar Sesión</span>
                </a>
            </div>
        </header>

<script>
    function actualizarReloj() {
        const ahora = new Date();
        let horas = ahora.getHours();
        const minutos = String(ahora.getMinutes()).padStart(2, '0');
        const segundos = String(ahora.getSeconds()).padStart(2, '0');
        const ampm = horas >= 12 ? 'PM' : 'AM';
        horas = horas % 12 || 12; 
        document.getElementById('reloj').innerHTML = `${String(horas).padStart(2, '0')}:${minutos}:${segundos} <span class="text-xs ml-1">${ampm}</span>`;
    }
    actualizarReloj();
    setInterval(actualizarReloj, 1000);
</script>
        