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
                        jQuery.ajax({
                            url: chatbotplugin_ajax.url,
                            type: 'POST',
                            data: {
                                action: 'chatbot_get_card_html',
                                residence: residence
                            },
                            success: function(cardHtml) {
                                const card = document.createElement('div');
                                card.classList.add('card');
                                card.innerHTML = cardHtml;
                                botResponse.appendChild(card);

                                // Add click event listener for "Voir les détails" buttons
                                botResponse.querySelectorAll('.card-details-button').forEach(button => {
                                    button.addEventListener('click', function() {
                                        const residenceId = this.getAttribute('data-id');
                                        fetchDetails(residenceId);
                                    });
                                });
                            }
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
            action: 'chatbot_get_details_content',
            details: { id: residenceId } // Example, adjust as needed
        },
        success: function(detailsContent) {
            const detailsWindow = window.open('', '_blank'); // Ouvrir une nouvelle fenêtre
            detailsWindow.document.open();
            detailsWindow.document.write(detailsContent);
            detailsWindow.document.close();
        },
        error: function(xhr, status, error) {
            console.error('AJAX Error:', status, error);
        }
    });
}
