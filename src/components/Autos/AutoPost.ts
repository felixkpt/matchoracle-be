import useAxios from '@/hooks/useAxios';
import { HttpVerbsType } from '@/interfaces/UncategorizedInterfaces';
import { publish, subscribe, unsubscribe } from '@/utils/events';
import { baseURL } from '@/utils/helpers';
import { useEffect, useState } from 'react';

const AutoPost = () => {
    const { response, post, put, destroy, patch } = useAxios();

    const [form, setForm] = useState<HTMLFormElement | null>(null);
    const [key, setKey] = useState(0);
    const [hasData, setHasData] = useState(false);

    const handleEvent = async (event: CustomEvent<{ [key: string]: any }>) => {
        setHasData(false);

        const rawForm = event.detail.target as HTMLFormElement;
        setForm(rawForm);

        const formElement = event.detail.nativeEvent.target as HTMLFormElement; // Get the form element
        const formData = new FormData(formElement); // Create a FormData object from the form
        const moreData = event.detail?.moreData || {};
        for (const key in moreData) {
            if (Array.isArray(moreData[key])) {
                const files = moreData[key];
                files.forEach((file: any) => {
                    formData.append(`${key}[]`, file || null);
                });
            } else {
                formData.append(`${key}`, moreData[key] || null);
            }
        }

        const elementId = rawForm.getAttribute('id') || rawForm.getAttribute('data-id') || rawForm?.closest('.modal')?.id || null;

        // Specify the URL where the post request will be sent
        let url = rawForm?.getAttribute('data-action') || ''; // Get the baseUri from the event detail
        url = baseURL('api/' + url);

        const method: HttpVerbsType = (rawForm?.getAttribute('method') || 'post').toLowerCase() as HttpVerbsType; // Get the form's HTTP method
        const submitButton = rawForm?.querySelector('button[type="submit"]') as HTMLButtonElement;

        if (submitButton) {
            if (submitButton.classList.contains('btn-saving')) return;
            submitButton.classList.add('disabled', 'btn-saving', 'cursor-progress');

            const submitBtnLoader = submitButton.querySelector('.submit-btn-loader');
            if (submitBtnLoader) {
                submitBtnLoader.classList.remove('d-none');
            }
        }

        let response;

        // Make the request
        if (method === 'post') {
            response = await post(url, formData, { elementId });
        } else if (method === 'put') {
            response = await put(url, formData, { elementId });
        } else if (method === 'patch') {
            response = await patch(url, formData, { elementId });
        } else if (method === 'delete') {
            response = await destroy(url, formData, { elementId });
        }

        console.log('elementId::::', elementId)
        publish('autoPostDone', { elementId, results: response });

        setKey(key + 1);

        if (submitButton) {
            submitButton.disabled = false; // Now TypeScript knows submitButton is HTMLButtonElement
            submitButton.classList.remove('disabled', 'btn-saving', 'cursor-progress');
            const submitBtnLoader = submitButton.querySelector('.submit-btn-loader');
            if (submitBtnLoader) {
                submitBtnLoader.classList.add('d-none');
            }
        }
    };

    useEffect(() => {
        setHasData(!!response.results || !!response.message);
    }, [response]);

    useEffect(() => {
        if (hasData && form) {
            const modal = form?.closest('.modal');

            if (modal && !modal.classList.contains('persistent-modal')) {
                const modalToggleBtn = modal.querySelector('button[data-bs-dismiss="modal"]') as HTMLButtonElement | null;

                if (modalToggleBtn) {
                    modalToggleBtn.click();
                    setForm(null);
                }
            }
            setHasData(false);
        }
    }, [hasData, key, form]);

    const eventListener = (event: CustomEvent<{ [key: string]: any }>) => {
        event.preventDefault();
        handleEvent(event);
    };

    useEffect(() => {
        unsubscribe('autoPost', eventListener as EventListener);
        subscribe('autoPost', eventListener as EventListener);

        // Cleanup the event listener when the component unmounts
        return () => {
            unsubscribe('autoPost', eventListener as EventListener);
        };
    }, [key]);

    return null;
};

export default AutoPost;
