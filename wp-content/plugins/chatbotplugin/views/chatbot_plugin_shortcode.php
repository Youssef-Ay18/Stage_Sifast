<div class="chat-container">
    <div class="chat-box" id="chat-box">
        <div class="message bot-message">
            Hi there!<br>How can I help you today?
        </div>
    </div>
    <div class="options">
        <button class="option-button" data-option="name">
            <i class="fas fa-home"></i> Nom de la résidence
        </button>
        <button class="option-button" data-option="city">
            <i class="fas fa-city"></i> Ville
        </button>
        <button class="option-button" data-option="budget">
            <i class="fas fa-dollar-sign"></i> Budget
        </button>
    </div>
    <div class="input-container">
        <input type="text" id="user-input" class="chat-input" placeholder="Type a message..." />
        <button onclick="sendMessage()" class="send-button">
            <i class="fas fa-paper-plane"></i> Envoyer
        </button>
    </div>
</div>
