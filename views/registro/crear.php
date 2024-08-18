<main class="registro paquetes">
    <h2 class="registro__heading"><?php echo $titulo ?></h2>
    <p class="registro__descripcion">Elige tu plan</p>

    <div class="cards">
    	<div class="paquetes__grid">
    	    <div data-aos="zoom-in-right" class="paquete">
				<div class="paquete__box">
    	        	<h3 class="paquete__nombre">Pase Gratis</h3>
    	        	<ul class="paquete__lista">
    	        	    <li class="paquete__elemento"><i class="paquete__circle fa-solid fa-circle-check"></i>Acceso Virtual a DevWebCamp</li>
    	        	</ul>
    	        	<span class="paquete__precio-registro">
						<span>
							$0
						</span>
					</span>
                    <form method="POST" action="/finalizar-registro/gratis">
                        <input type="submit" value="Inscripción Gratis" class="paquetes__submit">
                    </form>
				</div>	
    	    </div>

    	    <div data-aos="zoom-in-right" class="paquete">
				<div class="paquete__box">
    	        	<h3 class="paquete__nombre">Pase Presencial</h3>
    	        	<ul class="paquete__lista">
    	        	    <li class="paquete__elemento"><i class="paquete__circle fa-solid fa-circle-check"></i>Acceso Presencial a DevWebCamp</li>
    	        	    <li class="paquete__elemento"><i class="paquete__circle fa-solid fa-circle-check"></i>Pase por 2 días</li>
    	        	    <li class="paquete__elemento"><i class="paquete__circle fa-solid fa-circle-check"></i>Acceso a Talleres y Conferencias</li>
    	        	    <li class="paquete__elemento"><i class="paquete__circle fa-solid fa-circle-check"></i>Acceso a las grabaciones</li>
    	        	    <li class="paquete__elemento"><i class="paquete__circle fa-solid fa-circle-check"></i>Una camiseta del evento</li>
    	        	    <li class="paquete__elemento"><i class="paquete__circle fa-solid fa-circle-check"></i>Comida y Bebida</li>

    	        	</ul>
    	        	<span class="paquete__precio-registro">
						<span>
							$200 
						</span>
					</span>

					<div id="smart-button-container">
    					<div style="text-align: center;">
    					    <div id="paypal-button-container"></div>
    					</div>
					</div>
				</div>	
    	    </div>

    	    <div data-aos="zoom-in-right" class="paquete">
				<div class="paquete__box">
    	            <h3 class="paquete__nombre">Pase Virtual</h3>
    	            <ul class="paquete__lista">
    	                <li class="paquete__elemento"><i class="paquete__circle fa-solid fa-circle-check"></i>Acceso Virtual a DevWebCamp</li>
    	                <li class="paquete__elemento"><i class="paquete__circle fa-solid fa-circle-check"></i>Pase por 2 días</li>
    	                <li class="paquete__elemento"><i class="paquete__circle fa-solid fa-circle-check"></i>Acceso a Talleres y Conferencias</li>
    	                <li class="paquete__elemento"><i class="paquete__circle fa-solid fa-circle-check"></i>Acceso a las grabaciones</li>
    	            </ul>
    	            <span class="paquete__precio-registro">
						<span>
							$50 
						</span>
					</span>

					<div id="smart-button-container">
    					<div style="text-align: center;">
    					    <div id="paypal-button-container-virtual"></div>
    					</div>
					</div>
    	        </div>
    	    </div>
    	</div>
	</div>    
</main>




<!-- Reemplazar CLIENT_ID por tu client id proporcionado al crear la app desde el developer dashboard) -->
<script src="https://www.paypal.com/sdk/js?client-id=AWR-tqSUF2yHlOmlja7f4pCVQBoImP45G9eUYkeD9izpeOha49MMb2xw1V678IyPcdm0RbVWjzXnEAxX" data-sdk-integration-source="button-factory"></script>

 
<script>
    function initPayPalButton() {
      paypal.Buttons({
        style: {
          shape: 'rect',
          color: 'blue',
          layout: 'vertical',
          label: 'pay',
        },
 
        createOrder: function(data, actions) {
          return actions.order.create({
            purchase_units: [{"description":"1","amount":{"currency_code":"USD","value":200	}}]
          });
        },
 
        onApprove: function(data, actions) {
          return actions.order.capture().then(function(orderData) {
 
			const datos = new FormData();
			datos.append('paquete_id', orderData.purchase_units[0].description);
			datos.append('pago_id', orderData.purchase_units[0].payments.captures[0].id);

			fetch('/finalizar-registro/pagar', {
				method: 'POST', 
				body: datos
			})
			.then( respuesta => respuesta.json())
			.then(resultado => {
				if(resultado.resultado){
					actions.redirect('http://localhost:3000/finalizar-registro/conferencias')
				}
			})
          });
        },
 
        onError: function(err) {
          console.log(err);
        }
      }).render('#paypal-button-container');


	  // Pase virtual
      paypal.Buttons({
        style: {
			shape: 'rect',
          	color: 'blue',
          	layout: 'vertical',
          	label: 'pay',
        },

        createOrder: function(data, actions) {
          return actions.order.create({
            purchase_units: [{"description":"2","amount":{"currency_code":"USD","value":49}}]
          });
        },

        onApprove: function(data, actions) {
          return actions.order.capture().then(function(orderData) {

                const datos = new FormData();
                datos.append('paquete_id', orderData.purchase_units[0].description);
                datos.append('pago_id', orderData.purchase_units[0].payments.captures[0].id);

                fetch('/finalizar-registro/pagar', {
                    method: 'POST',
                    body: datos
                })
                .then( respuesta => respuesta.json())
                .then(resultado => {
                    if(resultado.resultado) {
                        actions.redirect('http://localhost:3000/finalizar-registro/conferencias');
                    }
                })
                
          });
        },

        onError: function(err) {
          console.log(err);
        }
      }).render('#paypal-button-container-virtual');
	}
 
  initPayPalButton();
</script>