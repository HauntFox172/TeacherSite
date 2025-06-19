document.getElementById('emailForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    const btn = document.getElementById('emailSubmitBtn');
    const originalText = btn.textContent;
    
    btn.disabled = true;
    btn.textContent = 'Отправка...';
    
    try {
        const formData = new FormData(this);
        
        // Добавляем отладочный вывод
        console.log('Отправляемые данные:', Object.fromEntries(formData));
        
        const response = await fetch('send_email.php', {
            method: 'POST',
            body: formData
        });
        
        // Отладочный вывод сырого ответа
        const rawResponse = await response.text();
        console.log('Сырой ответ:', rawResponse);
        
        try {
            const data = JSON.parse(rawResponse);
            if (data.success) {
                document.getElementById('response').innerHTML = `
                    <div class="alert alert-success">${data.message}</div>
                `;
                this.reset();
            } else {
                document.getElementById('response').innerHTML = `
                    <div class="alert alert-danger">${data.message}</div>
                `;
            }
        } catch (jsonError) {
            console.error('Ошибка парсинга JSON:', jsonError);
            document.getElementById('response').innerHTML = `
                <div class="alert alert-danger">Ошибка формата ответа: ${rawResponse}</div>
            `;
        }
    } catch (error) {
        document.getElementById('response').innerHTML = `
            <div class="alert alert-danger">Ошибка сети: ${error.message}</div>
        `;
    } finally {
        btn.disabled = false;
        btn.textContent = originalText;
    }
});