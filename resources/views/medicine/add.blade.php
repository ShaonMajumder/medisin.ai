@extends('layouts.app')

@section('title', 'Add Medicine')

@section('content')
    <form id="medicineForm">
        <div class="form-group">
            <label for="medicine_name">Medicine Name (Generic)</label>
            <input type="text" id="medicine_name" name="medicine_name" required placeholder="e.g., Sertaconazole Nitrate">
        </div>
        {{-- @if (auth()->user() && auth()->user()->role === 'shopkeeper')
            <div class="form-group">
                <label for="quantity">Quantity</label>
                <input type="number" id="quantity" name="quantity" min="0" placeholder="Enter quantity">
            </div>
        @endif --}}
        <button type="submit" id="fetchBtn">Fetch Details</button>
    </form>

    <div id="medicineDetails" class="medicine-details">
        <h2>Medicine Details</h2>
        <p><strong>Generic Name:</strong> <span id="generic_name"></span></p>
        <p><strong>Brand Names:</strong> <span id="brand_names"></span></p>
        <p><strong>Uses:</strong></p>
        <ul id="uses"></ul>
        <p><strong>Dosage:</strong> <span id="dosage"></span></p>
        <p><strong>Side Effects:</strong></p>
        <ul id="side_effects"></ul>
        <p><strong>Precautions:</strong></p>
        <ul id="precautions"></ul>
        <button id="confirmBtn" class="confirm-btn">Confirm Save</button>
    </div>
@endsection

@section('scripts')
    <script>
        const medicineForm = document.getElementById('medicineForm');
        const fetchBtn = document.getElementById('fetchBtn');
        const confirmBtn = document.getElementById('confirmBtn');
        const medicineDetails = document.getElementById('medicineDetails');

        // Display medicine details
        function displayDetails(details) {
            document.getElementById('generic_name').textContent = details.generic_name;
            document.getElementById('brand_names').textContent = details.brand_names.join(', ');
            const usesList = document.getElementById('uses');
            const sideEffectsList = document.getElementById('side_effects');
            const precautionsList = document.getElementById('precautions');
            usesList.innerHTML = details.uses.map(use => `<li>${use}</li>`).join('');
            sideEffectsList.innerHTML = details.side_effects.map(effect => `<li>${effect}</li>`).join('');
            precautionsList.innerHTML = details.precautions.map(precaution => `<li>${precaution}</li>`).join('');
            document.getElementById('dosage').textContent = details.dosage;
            medicineDetails.style.display = 'block';
        }

        // Handle form submission (fetch details)
        medicineForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            resetUI();
            fetchBtn.disabled = true;
            fetchBtn.classList.add('loading');

            const formData = new FormData(medicineForm);
            const data = {
                medicine_name: formData.get('medicine_name'),
                confirm_save: false,
                action: 'fetch'
            };
            if (formData.get('quantity')) {
                data.quantity = parseInt(formData.get('quantity')) || 0;
            }

            try {
                const response = await axios.post('/api/medicine/add', data, {
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json'
                    }
                });

                if (response.data.details) {
                    displayDetails(response.data.details);
                    showMessage('success', response.data.message);
                } else {
                    showMessage('error', 'Unexpected response from server');
                }
            } catch (error) {
                const errorMessage = error.response?.data?.error || 'Failed to fetch details';
                showMessage('error', errorMessage);
                console.error('Fetch error:', error);
            } finally {
                fetchBtn.disabled = false;
                fetchBtn.classList.remove('loading');
            }
        });

        // Handle confirm save
        confirmBtn.addEventListener('click', async () => {
            // if (!sanctumToken) return;

            resetUI();
            confirmBtn.disabled = true;
            confirmBtn.classList.add('loading');

            const data = {
                medicine_name: document.getElementById('medicine_name').value,
                confirm_save: true,
                action: 'save'
            };
            if (document.getElementById('quantity')) {
                data.quantity = parseInt(document.getElementById('quantity').value) || 0;
            }

            try {
                const response = await axios.post('/api/medicine/add', data, {
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json'
                    }
                });

                showMessage('success', response.data.message);
                medicineForm.reset();
            } catch (error) {
                const errorMessage = error.response?.data?.error || 'Failed to save medicine';
                showMessage('error', errorMessage);
                console.error('Save error:', error);
            } finally {
                confirmBtn.disabled = false;
                confirmBtn.classList.remove('loading');
            }
        });
    </script>
@endsection