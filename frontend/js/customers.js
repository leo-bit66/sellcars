
function showModal(message) {
    const modalMessage = document.getElementById('modal-message');
    const modal = document.getElementById('notice-modal');
    const okButton = document.getElementById('okButton');

    modalMessage.innerHTML = message;
    modal.style.display = 'block';

    okButton.addEventListener('click', () => {
        closeModal();

        if (message.includes('success') || message.includes('Partial')) {
            location.reload();
        }
    });
}

function closeModal() {
    const modal = document.getElementById('notice-modal');
    modal.style.display = 'none';
    const okButton = document.getElementById('okButton');
    okButton.removeEventListener('click', closeModal);
}

async function authenticatedFetch(url, method, body = null) {
    const token = localStorage.getItem('token');

    try {
        const response = await fetch(url, {
            method: method,
            headers: {
                'Authorization': `Bearer ${token}`,
                'Content-Type': 'application/json',
            },
            body: body ? JSON.stringify(body) : null,
        });

        const data = await response.json();

        if (response.ok) {
            return data;
        } else {
            throw data.error || 'An error occurred';
        }
    } catch (error) {
        throw error.message || 'An error occurred';
}
}

document.addEventListener('DOMContentLoaded', () => {

    const token = localStorage.getItem('token');
    if (token) {
        // Fetch and display customer data for the authenticated user
        fetchUserData();
    }
    fetchDataAndPopulateTable();

    async function fetchDataAndPopulateTable() {
        try {
            const data = await authenticatedFetch('/sellcars/customers-page/customers', 'GET');

            if (data) {
                populateTable(data);
                initializeDataTable();
            } else {
                console.error(data.error);
            }
        } catch (error) {
            console.error('An error occurred during the fetch operation:', error);
        }
    }

    function initializeDataTable() {
        var table = $('#customers-table').DataTable({
            "searching": true
        });

        // Custom search logic
        $('.search-bar input').on('keyup', function () {
            var searchTerm = $(this).val().toLowerCase();
            // Use DataTables API to perform the search
            table.search(searchTerm).draw();
        });
    }

    function populateTable(data) {
        const tbody = document.getElementById('customers-table').getElementsByTagName('tbody')[0];
        tbody.innerHTML = ''; // Clear existing rows

        data.forEach((customer, index) => {
            const row = `<tr>
                            <td>${index + 1}</td>
                            <td>${customer.first_name}</td>
                            <td>${customer.last_name}</td>
                            <td>${customer.company_name}</td>
                            <td>${customer.country}</td>
                            <td>${customer.zip_city}</td>
                            <td>${customer.street}</td>
                            <td>
                                <button class="edit-btn" onclick="editCustomer(${customer.id})" title="Edit this customer">
                                    <i class="fas fa-edit"></i> Edit
                                </button>
                                <button class="delete-btn" onclick="deleteCustomer(${customer.id})" title="Delete this customer">
                                    <i class="fas fa-trash"></i> Delete
                                </button>
                            </td>
                        </tr>`;

            tbody.innerHTML += row;
        });
    }

    async function fetchUserData() {
        try {
            const data = await authenticatedFetch('/sellcars/login/user', 'GET');
            const customerName = data.customerName;
            const lastLogin = data.lastLogin;

            // Update the DOM elements with the fetched data
            document.getElementById('customerNamePlaceholder').innerText = customerName;
            document.getElementById('lastLoginPlaceholder').innerText = lastLogin;
        } catch (error) {
            console.error('Error fetching customer data:', error);
        }
    }
});


document.addEventListener('DOMContentLoaded', () => {

    const uploadCustomerBtn = document.getElementById('uploadCustomerBtn');
    const uploadContactPersonsBtn = document.getElementById('uploadContactPersonsBtn');
    const uploadAddressesBtn = document.getElementById('uploadAddressesBtn');
    const fileInput = document.getElementById('fileInput');

    uploadCustomerBtn.addEventListener('click', () => {
        fileInput.dataset.uploadType = 'customers';
        fileInput.click();
    });

    uploadContactPersonsBtn.addEventListener('click', () => {
        fileInput.dataset.uploadType = 'contactpersons';
        fileInput.click();
    });

    uploadAddressesBtn.addEventListener('click', () => {
        fileInput.dataset.uploadType = 'addresses';
        fileInput.click();
    });

    fileInput.addEventListener('change', () => {
        handleFileUpload(fileInput.dataset.uploadType);
    });

    // Function to handle file upload
    function handleFileUpload(uploadType) {
        const selectedFile = fileInput.files[0];
        if (!selectedFile) {
            showModal('Error: No file selected.');
            return;
        }

        if (!selectedFile.name.toLowerCase().endsWith('.csv')) {
            showModal('Error: Only .csv files are allowed.');
            fileInput.value = '';
            return;
        }

        const maxSizeInBytes = 500 * 1024; // 500 KB
        if (selectedFile.size > maxSizeInBytes) {
            showModal('Error: File size exceeds the limit (500 KB). Please choose a smaller file.');
            fileInput.value = ''; // Clear the file input value
            return;
        }

        if (selectedFile) {
            const formData = new FormData();
            formData.append('csvFile', selectedFile);
            const token = localStorage.getItem('token');

            fetch(`/sellcars/uploads/${uploadType}`, {
                method: 'POST',
                headers: {
                    'Authorization': `Bearer ${token}`
                },
                body: formData
            })
                    .then(response => {
                        if (!response.ok) {
                            throw new Error('Error in network response');
                        }
                        return response.json();
                    })
                    .then(data => {
                        if (data.status === 'success') {
                            showModal('Success! ' + data.message);
                        } else if (data.status === 'partial_success') {
                            showModal('Partial Success! ' + data.message + ' Intnr values: ' + data.existing_customers.join(', '));
                        } else if (data.status === 'no_records_uploaded') {
                            showModal('No records uploaded. All records already exist.');
                        } else {
                            showModal('Error: ' + data.message);
                        }
                    })
                    .catch(error => {
                        // Display error message
                        showModal('Error uploading file. Please try again.');
                    })
                    .finally(() => {
                        // Clear the file input value after processing
                        fileInput.value = '';
                    });
        }
    }


    /** Close the modal if the user clicks outside of it
     window.addEventListener('click', (event) => {
     if (event.target === modal) {
     modal.style.display = 'none';
     }
     }); **/

});

function deleteCustomer(id) {
    const confirmation = window.confirm("Are you sure that you want to delete this customer?");
    if (confirmation) {
        // Display loading spinner while waiting for the server response
        const token = localStorage.getItem('token');
        const loadingSpinner = document.createElement('div');
        loadingSpinner.className = 'loading-spinner';
        document.body.appendChild(loadingSpinner);

        fetch(`/sellcars/customers-page/${id}`, {
            method: 'DELETE',
            headers: {
                'Authorization': `Bearer ${token}`,
                'Content-Type': 'application/json',
            },
        })
                .then(response => response.json())
                .then(data => {
                    document.body.removeChild(loadingSpinner);
                    // Display success or error message
                    if (data.success) {
                        alert(data.message);
                        // Reload the page
                        location.reload();
                    } else {
                        alert("Error deleting customer. Please try again.");
                    }
                })
                .catch(error => {
                    document.body.removeChild(loadingSpinner);
                    console.error('Error:', error);
                    alert("An unexpected error occurred. Please try again.");
                });
    } else {
        // TODO: Handle case when the user cancels the deletion
    }
}

function editCustomer(id) {
    const modal = document.getElementById('editModal');
    modal.style.display = 'block';
    document.getElementById('customerId').value = id;
    const token = localStorage.getItem('token');

    fetch(`/sellcars/customers-page/${id}`, {
        method: 'GET',
        headers: {
            'Authorization': `Bearer ${token}`,
            'Content-Type': 'application/json',
        },
    })
            .then(response => {
                if (response.ok) {
                    return response.json();
                } else {
                    throw new Error('Error fetching customer data');
                }
            })
            .then(customerData => {
                // Populate input fields with the fetched data
                document.getElementById('editFirstName').value = customerData.contact_persons[0].first_name;
                document.getElementById('editLastName').value = customerData.contact_persons[0].last_name;
                document.getElementById('editCompany').value = customerData.addresses[0].company_name;
                document.getElementById('editCountry').value = customerData.addresses[0].country;
                document.getElementById('editZipCity').value = customerData.addresses[0].zip + ' / ' + customerData.addresses[0].city;
                document.getElementById('editAddress').value = customerData.addresses[0].street;
            })
            .catch(error => {
                console.error('Error fetching customer data:', error);
                alert('Error fetching customer data. Please try again.');
                closeEditModal();
            });
}


function closeEditModal() {
    const modal = document.getElementById('editModal');
    modal.style.display = 'none';
}

function saveChanges() {
    const customerId = document.getElementById('customerId').value;
    const inputFields = document.querySelectorAll('#editModal input');
    const updatedData = {
        id: customerId,
    };
    inputFields.forEach((input) => {
        updatedData[input.id.replace('edit', '').toLowerCase()] = input.value;
    });

    const token = localStorage.getItem('token');
    const loadingSpinner = document.createElement('div');
    loadingSpinner.className = 'loading-spinner';
    document.body.appendChild(loadingSpinner);

    fetch(`/sellcars/customers-page/${customerId}`, {
        method: 'PUT',
        headers: {
            'Authorization': `Bearer ${token}`,
            'Content-Type': 'application/json',
        },
        body: JSON.stringify(updatedData),
    })
            .then(response => response.json())
            .then(data => {
                document.body.removeChild(loadingSpinner);
                const notification = document.getElementById('notification');

                if (data.success) {
                    showModal('Success! Changes saved successfully! Click OK to reload the page.' + data.message);
                } else {
                    showModal('Error! Error saving changes. Please try again.' + data.message);
                }
                closeEditModal();
            })
            .catch(error => {
                document.body.removeChild(loadingSpinner);
                showModal('Error! Error saving changes. Please try again.' + data.message);
                closeEditModal();
            });
}