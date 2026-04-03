const editModal = document.getElementById('editUserModal');
const editModalContent = document.getElementById('editUserModalContent');

// Set form values
const editUserId = document.getElementById('editUserId');
const editUserName = document.getElementById('editUserName');
const editUserRole = document.getElementById('edit_user_role');
const editDepartment = document.getElementById('edit_department');
const editUserEmail = document.getElementById('editUserEmail');
const editUserFirstName = document.getElementById('editUserFirstName');
const editUserMiddleName = document.getElementById('editUserMiddleName');
const editUserLastName = document.getElementById('editUserLastName');
const editUserBirthDate = document.getElementById('editUserBirthdate');
const editUserPicture = document.getElementById('editUserPicture');
const editUserPictureInput = document.getElementById('user_image');

function openEditUserModal(
  id,
  name,
  role,
  department,
  email,
  first_name,
  middle_name,
  last_name,
  birthdate,
  image_path,
) {
  editUserId.value = id;
  editUserName.value = name;
  editUserEmail.value = email;
  editUserFirstName.value = first_name;
  editUserMiddleName.value = middle_name || '';
  editUserLastName.value = last_name;
  editUserBirthDate.value = birthdate;
  editUserRole.value = role;
  editDepartment.value = department;
  if (image_path) {
    editUserPicture.innerHTML = `<img src="${image_path}" class="w-full h-full object-cover">`;
  } else {
    editUserPicture.innerHTML = `<span class="text-gray-500">No Image</span>`;
  }

  editModal.classList.remove('hidden');
  editModal.classList.add('flex');

  setTimeout(() => {
    editModalContent.classList.remove('opacity-0', 'scale-95');
    editModalContent.classList.add('opacity-100', 'scale-100');
  }, 10);
}

function closeEditUserModal() {
  editModalContent.classList.remove('opacity-100', 'scale-100');
  editModalContent.classList.add('opacity-0', 'scale-95');
  setTimeout(() => {
    editModal.classList.remove('flex');
    editModal.classList.add('hidden');
  }, 300);
}

if (editModal) {
  editModal.addEventListener('click', (event) => {
    if (event.target === editModal) closeEditUserModal();
  });
}

// Close filter dropdown on outside click
document.addEventListener('click', function (event) {
  const filter = document.getElementById('filter');
  const popup = document.getElementById('filterPopup');
  if (filter && popup && !popup.contains(event.target) && !filter.contains(event.target)) {
    filter.checked = false;
  }
});
