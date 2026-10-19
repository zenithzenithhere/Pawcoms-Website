const form = document.querySelector('#upload-form');
const imageInput = document.querySelector('#image');
const preview = document.querySelector('#preview');
const errorMessage = document.querySelector('#client-error');

const allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
const maximumSize = 2 * 1024 * 1024;
let previewUrl = '';

function clearPreview() {
    if (previewUrl) {
        URL.revokeObjectURL(previewUrl);
        previewUrl = '';
    }

    preview.removeAttribute('src');
    preview.hidden = true;
}

function validateImage(file) {
    if (!file) {
        return 'Please choose an image.';
    }

    if (!allowedTypes.includes(file.type)) {
        return 'Only JPG, PNG, GIF, and WebP images are allowed.';
    }

    if (file.size > maximumSize) {
        return 'The image must be 2 MB or smaller.';
    }

    return '';
}

imageInput.addEventListener('change', () => {
    clearPreview();
    errorMessage.hidden = true;

    const file = imageInput.files[0];
    const error = validateImage(file);

    if (error) {
        errorMessage.textContent = error;
        errorMessage.hidden = false;
        imageInput.value = '';
        return;
    }

    previewUrl = URL.createObjectURL(file);
    preview.src = previewUrl;
    preview.hidden = false;
});

form.addEventListener('submit', (event) => {
    const error = validateImage(imageInput.files[0]);

    if (error) {
        event.preventDefault();
        errorMessage.textContent = error;
        errorMessage.hidden = false;
    }
});
