@extends('layouts.app')

@section('title', 'Primary Cure')

@section('content')
    <div class="tabs">
        <div class="tab active" data-tab="medicine">Add Medicine To Box</div>
        <div class="tab" data-tab="symptoms">Analyze Primary Symptoms</div>
    </div>

    <div id="medicineTab" class="tab-content active">
        <form id="medicineForm">
            <div class="form-group">
                <label for="medicine_name">Medicine Name (Generic)</label>
                <input type="text" id="medicine_name" name="medicine_name" required placeholder="e.g., Sertaconazole Nitrate">
            </div>
            @if (auth()->user() && auth()->user()->role === 'shopkeeper')
                <div class="form-group">
                    <label for="quantity">Quantity</label>
                    <input type="number" id="quantity" name="quantity" min="0" placeholder="Enter quantity">
                </div>
            @endif
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
    </div>

    <div id="symptomsTab" class="tab-content">
        <form id="symptomsForm">
            <div class="form-group">
                <label for="image">Upload Image</label>
                <input type="file" id="image" name="image" accept="image/jpeg,image/png" required>
            </div>
            <div class="form-group">
                <label for="symptoms">Primary Symptoms</label>
                <textarea id="symptoms" name="symptoms" placeholder="e.g., fever, cough, headache"></textarea>
            </div>
            <button type="submit" id="symptomsBtn">Analyze Symptoms</button>
        </form>

        <div id="symptomsDetails" class="symptoms-details">
            <h2>Symptom Analysis Results</h2>
            <p><strong>Symptoms:</strong> <span id="symptoms_input"></span></p>
            <p><strong>Possible Conditions:</strong></p>
            <ul id="possible_conditions"></ul>
            <p><strong>Recommended Actions:</strong></p>
            <ul id="recommended_actions"></ul>
            <p><strong>Analyzed At:</strong> <span id="symptoms_analyzed_at"></span></p>
        </div>
    </div>
@endsection

@section('styles')
    <style>
        .tabs {
            display: flex;
            margin-bottom: 20px;
        }
        .tab {
            flex: 1;
            padding: 10px;
            text-align: center;
            cursor: pointer;
            font-weight: 500;
            color: var(--text-light, #546e7a);
            border-bottom: 2px solid transparent;
            transition: all 0.3s;
        }
        .tab.active {
            color: var(--primary, #1e88e5);
            border-bottom: 2px solid var(--primary);
        }
        .tab-content {
            display: none;
        }
        .tab-content.active {
            display: block;
        }
        .form-group {
            margin-bottom: 20px;
        }
        label {
            display: block;
            font-size: 0.95rem;
            font-weight: 500;
            color: var(--text-light, #546e7a);
            margin-bottom: 8px;
        }
        input[type="text"], input[type="number"], input[type="file"], textarea {
            width: 100%;
            padding: 12px;
            border: 1px solid #cfd8dc;
            border-radius: 8px;
            font-size: 1rem;
            transition: border-color 0.3s, box-shadow 0.3s;
        }
        textarea {
            min-height: 100px;
            resize: vertical;
        }
        input:focus, textarea:focus {
            outline: none;
            border-color: var(--primary, #1e88e5);
            box-shadow: 0 0 0 3px rgba(30, 136, 229, 0.1);
        }
        button {
            background: var(--primary, #1e88e5);
            color: white;
            padding: 12px 24px;
            border: none;
            border-radius: 8px;
            font-size: 1rem;
            font-weight: 500;
            cursor: pointer;
            transition: background 0.3s, transform 0.2s;
            position: relative;
            overflow: hidden;
        }
        button:hover {
            background: var(--primary-dark, #1565c0);
            transform: translateY(-2px);
        }
        button:disabled {
            background: #b0bec5;
            cursor: not-allowed;
        }
        .confirm-btn {
            background: var(--accent, #26a69a);
        }
        .confirm-btn:hover {
            background: var(--accent-dark, #00897b);
        }
        .medicine-details, .analysis-details, .symptoms-details {
            margin-top: 30px;
            padding: 20px;
            border: 1px solid #eceff1;
            border-radius: 12px;
            background: var(--card-bg, #ffffff);
            display: none;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
        }
        .medicine-details h2, .analysis-details h2, .symptoms-details h2 {
            font-size: 1.5rem;
            color: var(--text, #263238);
            margin-bottom: 15px;
        }
        .medicine-details p, .analysis-details p, .symptoms-details p {
            margin-bottom: 10px;
        }
        .medicine-details ul, .symptoms-details ul {
            list-style: none;
            padding-left: 10px;
        }
        .medicine-details li, .symptoms-details li {
            margin-bottom: 8px;
            position: relative;
            padding-left: 15px;
        }
        .medicine-details li::before, .symptoms-details li::before {
            content: '•';
            color: var(--primary, #1e88e5);
            position: absolute;
            left: 0;
        }
        .medicine-details strong, .analysis-details strong, .symptoms-details strong {
            color: var(--text, #263238);
            font-weight: 600;
        }
        .loading::after {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            width: 20px;
            height: 20px;
            border: 2px solid white;
            border-top-color: transparent;
            border-radius: 50%;
            animation: spin 0.8s linear infinite;
            transform: translate(-50%, -50%);
        }
        @keyframes spin {
            to { transform: translate(-50%, -50%) rotate(360deg); }
        }
        @media (max-width: 600px) {
            .tabs {
                flex-direction: column;
            }
            .tab {
                padding: 15px;
            }
            button {
                width: 100%;
                padding: 12px;
            }
        }
    </style>
@endsection

@section('scripts')
    <script>
        // const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
        // const sanctumToken = document.querySelector('meta[name="sanctum-token"]')?.content;

        // if (!sanctumToken) {
        //     console.error('Sanctum token not found. Ensure user is authenticated.');
        //     showMessage('error', 'Authentication error. Please log in again.');
        // }

        const medicineForm = document.getElementById('medicineForm');
        const symptomsForm = document.getElementById('symptomsForm');
        const fetchBtn = document.getElementById('fetchBtn');
        const symptomsBtn = document.getElementById('symptomsBtn');
        const confirmBtn = document.getElementById('confirmBtn');
        const medicineDetails = document.getElementById('medicineDetails');
        const symptomsDetails = document.getElementById('symptomsDetails');
        const tabs = document.querySelectorAll('.tab');
        const tabContents = document.querySelectorAll('.tab-content');

        // Tab switching
        tabs.forEach(tab => {
            tab.addEventListener('click', () => {
                tabs.forEach(t => t.classList.remove('active'));
                tabContents.forEach(c => c.classList.remove('active'));
                tab.classList.add('active');
                document.getElementById(`${tab.dataset.tab}Tab`).classList.add('active');
                resetUI();
            });
        });

        // Reset UI
        function resetUI() {
            medicineDetails.style.display = 'none';
            symptomsDetails.style.display = 'none';
            fetchBtn?.classList.remove('loading');
            symptomsBtn?.classList.remove('loading');
            confirmBtn?.classList.remove('loading');
        }

        // Display medicine details
        function displayMedicineDetails(details) {
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

        // Display symptom analysis results
        function displaySymptomsDetails(analysis) {
            document.getElementById('symptoms_input').textContent = analysis.symptoms;
            const conditionsList = document.getElementById('possible_conditions');
            const actionsList = document.getElementById('recommended_actions');
            conditionsList.innerHTML = analysis.analysis.possible_conditions.map(condition => `<li>${condition}</li>`).join('');
            actionsList.innerHTML = analysis.analysis.recommended_actions.map(action => `<li>${action}</li>`).join('');
            document.getElementById('symptoms_analyzed_at').textContent = analysis.analyzed_at;
            symptomsDetails.style.display = 'block';
        }

        // Handle medicine form submission (fetch details)
        medicineForm?.addEventListener('submit', async (e) => {
            e.preventDefault();
            // if (!sanctumToken) return;

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
                        {{-- 'Authorization': `Bearer ${sanctumToken}`, --}}
                        'Accept': 'application/json'
                    }
                });

                if (response.data.details) {
                    displayMedicineDetails(response.data.details);
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
        confirmBtn?.addEventListener('click', async () => {
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
                        {{-- 'Authorization': `Bearer ${sanctumToken}`, --}}
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

        // Handle symptoms form submission
        symptomsForm?.addEventListener('submit', async (e) => {
            e.preventDefault();
            // if (!sanctumToken) return;

            resetUI();
            symptomsBtn.disabled = true;
            symptomsBtn.classList.add('loading');

            const formData = new FormData(symptomsForm);

            try {
                const response = await axios.post('/api/patient/analyze-symptoms', formData, {
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        {{-- 'Authorization': `Bearer ${sanctumToken}`,- --}}
                        'Content-Type': 'multipart/form-data',
                        'Accept': 'application/json'
                    }
                });

                if (response.data.analysis) {
                    displaySymptomsDetails(response.data.analysis);
                    showMessage('success', response.data.message);
                } else {
                    showMessage('error', 'Unexpected response from server');
                }
            } catch (error) {
                const errorMessage = error.response?.data?.error || 'Failed to analyze symptoms';
                showMessage('error', errorMessage);
                console.error('Symptoms analysis error:', error);
            } finally {
                symptomsBtn.disabled = false;
                symptomsBtn.classList.remove('loading');
            }
        });
    </script>
@endsection