document.addEventListener('DOMContentLoaded', () => {
    // Attach event listeners to option buttons
    const optionButtons = document.querySelectorAll('.option-button');
    optionButtons.forEach(button => {
        button.addEventListener('click', function() {
            selectOption(this.getAttribute('data-option'));
        });
    });
});

let currentOption = '';

function selectOption(option) {
    currentOption = option;
    const chatBox = document.getElementById('chat-box');

    // User message
    let userMessageText;
    let botResponseText;
    if (option === 'name') {
        userMessageText = 'Nom de la résidence';
        botResponseText = 'Please provide the name of the residence.';
    } else if (option === 'city') {
        userMessageText = 'Ville';
        botResponseText = 'Please provide the city.';
    } else if (option === 'budget') {
        userMessageText = 'Budget';
        botResponseText = 'Please provide your budget';
    }

    const userMessage = document.createElement('div');
    userMessage.classList.add('message', 'user-message');
    userMessage.innerText = userMessageText;
    chatBox.appendChild(userMessage);

    // Bot response
    const botResponse = document.createElement('div');
    botResponse.classList.add('message', 'bot-message');
    botResponse.innerText = botResponseText;
    chatBox.appendChild(botResponse);

    chatBox.scrollTop = chatBox.scrollHeight;
}

function sendMessage() {
    const chatBox = document.getElementById('chat-box');
    const userInput = document.getElementById('user-input');
    const userText = userInput.value.trim();

    if (userText !== '') {
        const userMessage = document.createElement('div');
        userMessage.classList.add('message', 'user-message');
        userMessage.innerText = userText;
        chatBox.appendChild(userMessage);

        userInput.value = '';

        jQuery.ajax({
            url: chatbotplugin_ajax.url,
            type: 'POST',
            data: {
                action: 'chatbot_plugin_handle_message',
                query: userText,
                option: currentOption
            },
            success: function(response) {
                console.log(response); // Check the response data

                const botResponse = document.createElement('div');
                botResponse.classList.add('message', 'bot-message');

                if (response.data && Array.isArray(response.data.results) && response.data.results.length > 0) {
                    response.data.results.forEach(residence => {
                        const card = document.createElement('div');
                        card.classList.add('card');
                        card.innerHTML = `
                            <img src="${residence.picture}" alt="${residence.title}" class="card-img">
                            <div class="card-body">
                                <h3 class="card-title">${residence.title}</h3>
                                <p class="card-address">${residence.address}</p>
                                <p class="card-price">${residence.price}</p>
                                <button class="card-details-button" data-id="${residence.id}">Voir les détails</button>
                            </div>
                        `;
                        botResponse.appendChild(card);
                    });

                    // Add click event listener for "Voir les détails" buttons
                    botResponse.querySelectorAll('.card-details-button').forEach(button => {
                        button.addEventListener('click', function() {
                            const residenceId = this.getAttribute('data-id');
                            fetchDetails(residenceId);
                        });
                    });

                } else {
                    botResponse.innerText = response.data ? response.data.message : 'Aucune résidence trouvée.';
                }

                chatBox.appendChild(botResponse);
                chatBox.scrollTop = chatBox.scrollHeight;
                
                // Re-afficher les options après le message
                const options = document.querySelector('.options');
                options.style.display = 'flex';
            },
            error: function(xhr, status, error) {
                console.error('AJAX Error:', status, error); // Check AJAX errors
            }
        });
    }
}

function fetchDetails(residenceId) {
    jQuery.ajax({
        url: chatbotplugin_ajax.url,
        type: 'POST',
        data: {
            action: 'chatbot_plugin_handle_details',
            residence_id: residenceId
        },
        success: function(response) {
            if (response.success) {
                const details = response.data;
                const detailsWindow = window.open('', '_blank'); // Ouvrir une nouvelle fenêtre
                const detailsContent = `
                    <!DOCTYPE html>
                    <html lang="en">
                    <head>
                        <meta charset="UTF-8">
                        <meta name="viewport" content="width=device-width, initial-scale=1.0">
                        <title>Détails de la Résidence</title>
                        <style>
                            .apisearchplugin-details {
                              max-width: 800px;
                              margin: 0 auto;
                              padding: 40px;
                              background-color: #fff;
                              border: 1px solid #ddd;
                              border-radius: 12px;
                              box-shadow: 0 2px 12px rgba(0, 0, 0, 0.2);
                              display: flex;
                              flex-direction: column;
                              align-items: center;
                              font-family: 'Open Sans', sans-serif;
                            }

                            .apisearchplugin-details h2 {
                              font-size: 36px;
                              font-weight: bold;
                              margin-top: 0;
                              color: #333;
                              text-align: center;
                              margin-bottom: 20px;
                              font-family: 'Montserrat', sans-serif;
                            }

                            .apisearchplugin-details img {
                              width: 100%;
                              height: auto;
                              margin-bottom: 40px;
                              border-radius: 12px;
                              box-shadow: 0 2px 12px rgba(0, 0, 0, 0.2);
                            }

                            .apisearchplugin-details-offers,
                            .apisearchplugin-details-services {
                              list-style: none;
                              padding-left: 0;
                              margin-bottom: 40px;
                            }

                            .apisearchplugin-details-offers li,
                            .apisearchplugin-details-services li {
                              margin-bottom: 20px;
                              padding: 20px;
                              border-bottom: 1px solid #ddd;
                              border-radius: 12px;
                              background-color: #f7f7f7;
                              box-shadow: 0 2px 12px rgba(0, 0, 0, 0.1);
                              font-size: 16px;
                              font-family: 'Lato', sans-serif;
                            }

                            .apisearchplugin-details-offers li:before,
                            .apisearchplugin-details-services li:before {
                              content: "";
                              display: inline-block;
                              width: 24px;
                              height: 24px;
                              margin-right: 10px;
                              background-color: #0073aa;
                              border-radius: 50%;
                              font-size: 18px;
                              color: #fff;
                              text-align: center;
                              line-height: 24px;
                            }

                            .apisearchplugin-details-offers li:hover,
                            .apisearchplugin-details-services li:hover {
                              background-color: #fff;
                              box-shadow: 0 2px 12px rgba(0, 0, 0, 0.2);
                            }

                            .apisearchplugin-details-preview {
                              margin-bottom: 40px;
                              padding: 20px;
                              border: 1px solid #ddd;
                              border-radius: 12px;
                              background-color: #f7f7f7;
                              box-shadow: 0 2px 12px rgba(0, 0, 0, 0.1);
                              font-size: 16px;
                              font-family: 'Lato', sans-serif;
                            }

                            .apisearchplugin-details-preview h3 {
                              font-size: 24px;
                              font-weight: bold;
                              margin-top: 0;
                              color: #333;
                              margin-bottom: 10px;
                              font-family: 'Montserrat', sans-serif;
                            }

                            .apisearchplugin-details-preview p {
                              font-size: 16px;
                              color: #666;
                              margin-bottom: 20px;
                            }

                            .apisearchplugin-details-preview ul {
                              list-style: none;
                              padding-left: 0;
                              margin-bottom: 20px;
                            }

                            .apisearchplugin-details-preview li {
                              margin-bottom: 10px;
                              padding: 10px;
                              border-bottom: 1px solid #ddd;
                            }

                            .apisearchplugin-details-preview li:last-child {
                              border-bottom: none;
                            }

                            .apisearchplugin-details-preview li:before {
                              content: "";
                              display: inline-block;
                              width: 24px;
                              height: 24px;
                              margin-right: 10px;
                              background-color: #0073aa;
                              border-radius: 50%;
                              font-size: 18px;
                              color: #fff;
                              text-align: center;
                              line-height: 24px;
                            }
                        </style>


                    </head>
                    <body>
                        <div class="apisearchplugin-details">
                            <h2>${details.title}</h2>
                            <img src="${details.picture}" alt="${details.title}">
                            <p>Adresse : ${details.address}</p>
                            <h3>Offres</h3>
                            <ul class="apisearchplugin-details-offers">
                                ${details.offers.map(offer => `<li>${offer.optional_comment_equipped}</li>`).join('')}
                            </ul>
                            <h3>Aperçu</h3>
                            <div class="apisearchplugin-details-preview">
                                <h3>Surface</h3>
                                <p>${details.preview.surface_from} - ${details.preview.surface_to} m²</p>
                                <h3>Loyer à partir de</h3>
                                <p>${details.preview.rent_amount_from} €</p>
                                <h3>Nombre de logements</h3>
                                <p>${details.preview.quantity}</p>
                                <h3>Services</h3>
                                <ul>
                                    ${details.preview.residence_services.map(service => `<li>${service.title}: ${service.description} (${service.price})</li>`).join('')}
                                </ul>
                            </div>
                        </div>
                    </body>
                    </html>
                `;
                detailsWindow.document.open();
                detailsWindow.document.write(detailsContent);
                detailsWindow.document.close();
            } else {
                console.error('Error fetching details:', response.data.message);
            }
        },
        error: function(xhr, status, error) {
            console.error('AJAX Error:', status, error);
        }
    });
}
