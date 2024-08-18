(function(){
    const ponentesInput = document.querySelector('#ponentes');

    if(ponentesInput){
        let ponentes = [];
        let ponentesFiltrados = [];
        
        const listadoPonentes = document.querySelector('#listado-ponentes');
        const ponenteHidden = document.querySelector('[name="ponente_id"]')

        obtenerPonentes();
        ponentesInput.addEventListener('input', buscarPonentes)

        if(ponenteHidden.value){
            (async () => {
                const ponente = await obtenerPonente(ponenteHidden.value)
                const {nombre, apellido} = ponente

                //Insertar en el HTML
                const ponenteDOM = document.createElement('LI');
                ponenteDOM.classList.add('listado-ponentes__ponente','listado-ponentes__ponente--seleccionado');
                ponenteDOM.textContent = `${nombre} ${apellido}`

                listadoPonentes.appendChild(ponenteDOM)
            })()
        }

        async function obtenerPonentes(){
            const url = `/api/ponentes`;
            const respuesta = await fetch(url);
            const resultado = await respuesta.json();

            formatearPonentes(resultado)
        }

        async function obtenerPonente(id){
            const url = `/api/ponente?id=${id}`;
            const respuesta = await fetch(url)
            const resultado = await respuesta.json()
            return resultado;
        }

        function formatearPonentes(arrayPonentes = []){
            //me traigo del arreglo los campos de ID y nombre y apellido
            ponentes = arrayPonentes.map( ponente => {
                return {
                    nombre: `${ponente.nombre.trim()} ${ponente.apellido.trim()}`,
                    id: ponente.id
                }
            })
        }

        function buscarPonentes(e){
            const busqueda = e.target.value;

            if(busqueda.length > 3) {
                //creo una expresion regular
                const expresion = new RegExp(busqueda, "i");

                //Comprobar el nombre del ponente independientemente si esta en mayuscula o minusculada 
                //si es diferente a -1 en contro el ponente
                ponentesFiltrados = ponentes.filter(ponente => {
                    if(ponente.nombre.toLowerCase().search(expresion) != -1) {
                        return ponente
                    }
                })
            } else {
                ponentesFiltrados = [];
            } 

            mostrarPonentes();
        }

        function mostrarPonentes(){

            //busco un nombre lo escribo, lo borro y vuelvo a escribir otro nombre el anterior nombre se borra y queda el nuevo
            while(listadoPonentes.firstChild){
                listadoPonentes.removeChild(listadoPonentes.firstChild)
            }

            if(ponentesFiltrados.length > 0){
                ponentesFiltrados.forEach(ponente => {
                    const ponenteHTML = document.createElement('LI');
                    ponenteHTML.classList.add('listado-ponentes__ponente')
                    ponenteHTML.textContent = ponente.nombre;
                    ponenteHTML.dataset.ponenteId = ponente.id
                    ponenteHTML.onclick = seleccionarPonente
    
                    // Añadir al dom
                    listadoPonentes.appendChild(ponenteHTML)
                })
            } else {
                const noResultados = document.createElement('P');
                noResultados.classList.add('listado-ponentes__no-resultado')
                noResultados.textContent = 'No Hay Resultados para tu búsqueda'

                listadoPonentes.appendChild(noResultados);
            }
            
        }

        function seleccionarPonente(e){
            const ponente = e.target;

            //remover la clase previa
            const ponentePrevio = document.querySelector('.listado-ponentes__ponente--seleccionado')
            if(ponentePrevio){
                ponentePrevio.classList.remove('listado-ponentes__ponente--seleccionado')
            } 

            ponente.classList.add('listado-ponentes__ponente--seleccionado')

            //asignar ponente
            ponenteHidden.value = ponente.dataset.ponenteId
        }
    }
})();
