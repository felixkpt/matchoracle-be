const showErrors = (data: any, validationElementId: string | null = null) => {
    const { errors } = data;

    if (errors && Object.keys(errors).length > 0) {
        for (let key in errors) {
            let input = key;

            // Map specific keys to their corresponding input names
            switch (key) {
                case 'email':
                    input = 'username';
                    break;
                case 'start_date':
                    input = 's';
                    break;
                case 'end_date':
                    input = 't';
                    break;
                default:
                    break;
            }

            // Get the validation element
            const validationElement = validationElementId !== null
                ? document.getElementById(validationElementId)
                : document;

            if (!validationElement) return;

            // Find the element associated with the input
            let element: Element | null;
            element = validationElement.querySelector(`[name="${input}"]`)
                || validationElement.querySelectorAll(`[name="${key}"]`)[0];

            if (!element) {
                element = validationElement.querySelector('#' + input)
                    || validationElement.querySelector('#' + key);
            }

            if (!element || element === null) continue;

            // Handle hidden and d-none elements
            if (
                element.getAttribute('type') === 'hidden' ||
                element.classList.contains('hidden') ||
                element.classList.contains('d-none')
            ) {
                element = element.closest('.form-control');
            }

            if (!element || element === null) continue;

            // Add 'is-invalid' class to the element
            element.classList.add('is-invalid');

            // Create and append invalid feedback
            const invalidFeedback = document.createElement('div');
            invalidFeedback.classList.add('invalid-feedback');
            invalidFeedback.innerHTML = errors[key];
            const formGroup = element.closest('.form-group') || element.closest('.form-floating');

            if (!formGroup) continue

            formGroup.classList.add('has-error');
            formGroup.append(invalidFeedback);

            // Add event listeners to remove 'is-invalid' class on focus and click
            element.addEventListener('focus', function () {
                if (!element) return
                element.classList.remove('is-invalid');
                invalidFeedback.remove();
            });

            element.addEventListener('click', function () {
                if (!element) return
                element.classList.remove('is-invalid');
                invalidFeedback.remove();
            });
        }
    }
};

const clearErrors = (validationElementId: string | null = null) => {
    const validationElement = validationElementId ? document.getElementById(validationElementId) : document.body;
    if (!validationElement) return;

    // Remove 'is-invalid' class and related elements
    validationElement.querySelectorAll('.is-invalid').forEach(element => {
        element.classList.remove('is-invalid');
        const el = element.closest('.form-group') || element.closest('.form-floating');
        if (!el) return
        
        el.classList.remove('has-error');
        const invalidFeedback = el.querySelector('.invalid-feedback');
        if (invalidFeedback) {
            invalidFeedback.remove();
        }
    });
};

export { showErrors, clearErrors };
