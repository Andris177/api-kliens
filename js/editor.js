function openEditor(id, name) {
    document.getElementById('id').value = id;
    document.getElementById('name').value = name;
    document.getElementById('editor-row').classList.remove('hidden');
}

function closeEditor() {
    document.getElementById('editor-row').classList.add('hidden');
    document.getElementById('id').value = '';
    document.getElementById('name').value = '';
}

function saveChanges() {
    const id = document.getElementById('id').value;
    const name = document.getElementById('name').value;
    alert("Mentett adatok: " + id + " - " + name);
    document.getElementById('editor-row').classList.add('hidden');
}
