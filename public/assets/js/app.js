fetch('/DriverLux/public/api/veiculos')
    .then(res => res.json())
    .then(data => {
        const container = document.getElementById('car-list');

        data.data.forEach(car => {
            const card = document.createElement('div');
            card.classList.add('car-card');

            card.innerHTML = `
                <img src="${car.imagem_url}" width="100%">
                <h3>${car.marca} ${car.modelo}</h3>
                <p>${car.categoria_nome}</p>
                <strong>R$ ${car.valor_base_diaria}/dia</strong>
            `;

            container.appendChild(card);
        });
    })
    .catch(err => console.error(err));