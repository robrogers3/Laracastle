<script src="https://d2t77mnxyo7adj.cloudfront.net/v1/c.js?{{ config('laracastle.castle.app_id') }}"></script>
<script>
    function onSubmit(myForm) {
        // The token is continously updated so fetch it right before form submit
        var clientId = _castle('getClientId');
        // Populate a hidden field called `castle_client_id`
        var hiddenInput = document.createElement('input');
        hiddenInput.setAttribute('type', 'hidden');
        hiddenInput.setAttribute('name', 'castle_client_id');
        hiddenInput.setAttribute('value', clientId);

        // Add the `castle_client_id` into the form so it gets sent to the server
        myForm.appendChild(hiddenInput);
    };
    document.addEventListener('DOMContentLoaded', (event) => {
        if (document.getElementById('password')) {
            document.getElementById('password').form.addEventListener('submit', (e) => {
                onSubmit(e.currentTarget);
           }, false)
        }
    })
</script>
@auth
<script>
    _castle('identify', '{{ Auth::id() }}');
</script>
@endauth