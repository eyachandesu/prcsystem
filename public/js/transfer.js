document.getElementById('departments').addEventListener('change', function () {
  const deptId = this.value;
  const userSelect = document.getElementById('user');

  if (!deptId || deptId === '') {
    userSelect.disabled = true;
    userSelect.classList.add('bg-gray-100', 'cursor-not-allowed');
    userSelect.innerHTML = '<option value="" disabled selected>Select Department First</option>';
    return;
  }

  // 2. We have a valid ID, so enable and fetch
  userSelect.disabled = false;
  userSelect.classList.remove('bg-gray-100', 'cursor-not-allowed');
  userSelect.innerHTML = '<option value="" disabled selected>Loading users...</option>';

  const fetchUrl = `../functions/get_users.php?dept_id=${deptId}`;

  fetch(fetchUrl)
    .then((response) => response.json())
    .then((responseObject) => {
      console.log('Server Response:', responseObject);

      // Access the 'data' array from our debug object
      const users = responseObject.data || responseObject;

      userSelect.innerHTML = '<option value="" disabled selected>Select User</option>';

      if (users.length === 0) {
        userSelect.innerHTML = '<option value="" disabled>No other users found</option>';
      } else {
        users.forEach((user) => {
          const option = document.createElement('option');
          option.value = user.user_id;
          option.textContent = `${user.user_first_name} ${user.user_last_name} `;
          userSelect.appendChild(option);
        });
      }
    })
    .catch((error) => {
      console.error('Fetch Error:', error);
      userSelect.innerHTML = '<option value="" disabled>Error loading users</option>';
    });
});

const deptDropdown = document.getElementById('departments');
if (deptDropdown.value && deptDropdown.value !== '') {
  deptDropdown.dispatchEvent(new Event('change'));
}
