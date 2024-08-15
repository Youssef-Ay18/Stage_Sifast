document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('search');
    const autocompleteContainer = document.createElement('div');
    autocompleteContainer.className = 'autocomplete-suggestions';
    searchInput.parentNode.appendChild(autocompleteContainer);

    searchInput.addEventListener('input', function() {
        const query = searchInput.value;
        if (query.length < 2) {
            autocompleteContainer.innerHTML = '';
            return;
        }

        fetch(searchplugin_params.ajax_url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: new URLSearchParams({
                action: 'searchplugin_autocomplete',
                search: query,
            }),
        })
        .then(response => response.json())
        .then(data => {
            autocompleteContainer.innerHTML = '';
            if (data.length > 0) {
                data.forEach(item => {
                    const suggestion = document.createElement('div');
                    suggestion.className = 'autocomplete-suggestion';
                    suggestion.innerHTML = `<span class="suggestion-text">${item.name}</span>`;
                    suggestion.addEventListener('click', function() {
                        searchInput.value = item.name;
                        autocompleteContainer.innerHTML = '';
                    });
                    autocompleteContainer.appendChild(suggestion);
                });
            } else {
                autocompleteContainer.innerHTML = '<div class="autocomplete-suggestion">No results found</div>';
            }
        });
    });

    document.addEventListener('click', function(event) {
        if (!autocompleteContainer.contains(event.target) && event.target !== searchInput) {
            autocompleteContainer.innerHTML = '';
        }
    });
});
