(function(){
    const horas = document.querySelector('#horas');
    if(horas){
        

        const categoria = document.querySelector('[name="categoria_id"]');
        const dias = document.querySelectorAll('[name="dia"]');
        const inputHiddenDia = document.querySelector('[name="dia_id"]');
        const inputHiddenHora = document.querySelector('[name="hora_id"]');

        //llenan el objeto de busqueda, categoria llena si es workshop o conferencias y dias si es viernes o sabado
        categoria.addEventListener('change', terminoBusqueda)
        dias.forEach( dia => dia.addEventListener('change', terminoBusqueda))

        //creo un objeto en memoria y los uso para comparar si estan vacios
        let busqueda = {
            categoria_id: +categoria.value || '',
            dia: +inputHiddenDia.value || '',
        }

        if(!Object.values(busqueda).includes('')){

            (async() =>{
                await buscarEventos();

                //lo hago para no tener que poner todo el input 
                const id = inputHiddenHora.value
                   
                //resaltar la hora que esta tomada por vos
                const horaSeleccionada = document.querySelector(`[data-hora-id ="${id}"]`)
                   
                horaSeleccionada.classList.remove('horas__hora--deshabilitada')
                horaSeleccionada.classList.add('horas__hora--seleccionada')

                horaSeleccionada.onclick = seleccionarHora;

            })()
        }

        //con esto mapeo los campos y llenan el atributo del objeto
        function terminoBusqueda(e){
            busqueda[e.target.name] = e.target.value;

            //reiniciar los campos ocultos y el selector de horas
            inputHiddenHora.value = '';
            inputHiddenDia.value = ''; 
            const horaPrevia = document.querySelector('.horas__hora--seleccionada')

            if(horaPrevia){
                horaPrevia.classList.remove('horas__hora--seleccionada')
            }

            //nos permite acceder a los valores del objeto de busqueda
            // si al menos uno de los dos campos estan vacios previene la ejecucion de la funcion buscarEventos
            if(Object.values(busqueda).includes('')){
                return
            }

            buscarEventos();
        }
     
        async function buscarEventos(){
            const{dia, categoria_id} = busqueda
            const url = `/api/eventos-horario?dia_id=${dia}&categoria_id=${categoria_id}`;

            const resultado = await fetch(url);
            const eventos = await resultado.json();

            obtenerHorasDisponibles(eventos);
        }
        function obtenerHorasDisponibles(eventos){
            //Reiniciar las horas
            const listadoHoras = document.querySelectorAll('#horas li');
            listadoHoras.forEach(li => li.classList.add('horas__hora--deshabilitada'));

            //Comprobar Eventos ya tomados, y quitar la variable de deshabilitado 
            //obtiene los id de las horas que ya estan tomadas
            const horasTomadas = eventos.map(evento => evento.hora_id);
            //convertir un NodeList en un arreglo
            const listadoHorasArray = Array.from(listadoHoras); 

            //filter y mezclando con includes comparo el arreglo de horasTomadas con dataset entonces retornalo en resultado 
            const resultado = listadoHorasArray.filter( li => !horasTomadas.includes(li.dataset.horaId ));
            //itero con el forEach todos los li y remuevo la clase horas__hora--deshabilitada para los que esten desocupados de las horas
            resultado.forEach(li => li.classList.remove('horas__hora--deshabilitada'));
            


            const horasDisponibles = document.querySelectorAll('#horas li:not(.horas__hora--deshabilitada)');
            horasDisponibles.forEach(hora => hora.addEventListener('click', seleccionarHora));
     
        }
        function seleccionarHora(e){
            //desabilitar la hora previa, si hay un nuevo click 
            const horaPrevia = document.querySelector('.horas__hora--seleccionada')
            if(horaPrevia){
                horaPrevia.classList.remove('horas__hora--seleccionada')
            }
            //agregar clase de seleccionado
            e.target.classList.add('horas__hora--seleccionada')

            inputHiddenHora.value = e.target.dataset.horaId

            //Llenar el campo oculto de dia
            inputHiddenDia.value = document.querySelector('[name="dia"]:checked').value
        }
    }
})();
