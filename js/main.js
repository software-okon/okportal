document.addEventListener('DOMContentLoaded', function() {
    // Keresési automatikus kiegészítés
    const searchInput = document.getElementById('mainSearch');
    const suggestionsDiv = document.getElementById('searchSuggestions');
    let searchTimeout;

    if (searchInput) {
        searchInput.addEventListener('input', function() {
            clearTimeout(searchTimeout);
            const q = this.value.trim();
            if (!suggestionsDiv) return;
            if (q.length < 2) { suggestionsDiv.style.display = 'none'; return; }
            searchTimeout = setTimeout(async () => {
                try {
                    const res = await fetch('/kereses_ajax.php?type=suggest&q=' + encodeURIComponent(q));
                    const data = await res.json();
                    if (data.success && data.data.suggestions.length > 0) {
                        suggestionsDiv.innerHTML = data.data.suggestions.map(s =>
                            `<div style="background:white;padding:10px 16px;border-bottom:1px solid #eee;cursor:pointer;border-radius:4px;margin-bottom:2px;" onclick="window.location.href='${s.url}'">${s.cim} <span style="color:#999;font-size:0.8rem;">(${s.fokategoria})</span></div>`
                        ).join('');
                        suggestionsDiv.style.display = 'block';
                    } else { suggestionsDiv.style.display = 'none'; }
                } catch(e) { suggestionsDiv.style.display = 'none'; }
            }, 300);
        });
        document.addEventListener('click', function(e) {
            if (suggestionsDiv && !suggestionsDiv.contains(e.target) && e.target !== searchInput) suggestionsDiv.style.display = 'none';
        });
    }

    // Kedvenc toggle
    window.toggleKedvenc = async function(hirdetesId) {
        const btn = document.getElementById('kedvencBtn');
        if (!btn) return;
        const isKedvenc = btn.textContent.includes('❤️');
        const formData = new FormData();
        formData.append('hirdetes_id', hirdetesId);
        formData.append('action', isKedvenc ? 'remove' : 'add');
        try {
            const res = await fetch('/kedvencek.php', { method: 'POST', body: formData });
            const data = await res.json();
            if (data.success) btn.textContent = isKedvenc ? '🤍 Kedvencekhez adás' : '❤️ Kedvencekben';
        } catch(e) { alert('Hiba történt!'); }
    };

    // Üzenetküldés
    window.sendMessage = async function(hirdetesId) {
        const status = document.getElementById('msgStatus');
        if (!status) return;
        try {
            const formData = new FormData();
            formData.append('hirdetes_id', hirdetesId);
            formData.append('kuldo_nev', document.getElementById('msgNev')?.value || '');
            formData.append('kuldo_email', document.getElementById('msgEmail')?.value || '');
            formData.append('kuldo_telefon', document.getElementById('msgTelefon')?.value || '');
            formData.append('targy', document.getElementById('msgTargy')?.value || '');
            formData.append('uzenet', document.getElementById('msgUzenet')?.value || '');
            const res = await fetch('/uzenet_kuldes.php', { method: 'POST', body: formData });
            const data = await res.json();
            if (data.success) {
                status.textContent = '✅ Üzenet elküldve!'; status.style.color = '#27ae60';
                const form = document.getElementById('messageForm');
                if (form) form.innerHTML = '<p style="color:#27ae60;text-align:center;padding:20px;">✅ Üzenet sikeresen elküldve!</p>';
            } else {
                status.textContent = '❌ ' + data.message; status.style.color = '#c0392b';
            }
        } catch(e) { status.textContent = '❌ Hálózati hiba!'; status.style.color = '#c0392b'; }
    };
});