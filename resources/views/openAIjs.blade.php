<script>
 function sendMessage(route) {

    var message = document.getElementById('message').value;

        fetch(route, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({ message: message })
        })
        .then(res => res.json())
        .then(data => {
            document.getElementById('result').innerHTML = data.result ?? 'No result';
        })
        .catch(err => console.error("PARSE ERROR:", err));
 }
</script>
