<main class="pagina">
    <h2 class="pagina__heading"><?php echo $titulo ?></h2>
    <p class="pagina__descripcion">Tu Boleto - Te recomendamos almacenarlo, puedes compartirlo en redes sociales</p>
    <div class="ticket ticket__virtual">

        <div class="tickets tickets--left ticket--<?php echo strtolower($registro->paquete->nombre); ?> tickets--acceso">
            <div class="ticket__caja">
                <h3 class="ticket__caja__logo">&#60;DevWebCamp/></h3>
            </div>    
            <div class="ticket__cuerpo">
                <h4 class="ticket__cuerpo__titulo">Tipo de Paquete : </h4>
                <p class="ticket__cuerpo__plan"> $0 - <?php echo $registro->paquete->nombre; ?></p>
                <h4 class="ticket__cuerpo__titulo">Nombre y Apellido : </h4>
                <p class="ticket__cuerpo__nombre"><?php echo $registro->usuario->nombre . " " . $registro->usuario->apellido ; ?></p>
            </div>    
        </div>    

        <div class="tickets tickets--right">
            <p class="ticket__codigo"><?php echo '#' .  $registro->token; ?></p>
            <div class="barcode-box">
                <div class="barcode"></div>
            </div>    
        </div>
    </div>
</main>

