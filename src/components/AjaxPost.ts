import useAxios from '@/hooks/useAxios';
import { HttpVerbsType } from '@/interfaces/UncategorizedInterfaces';
import { publish, subscribe, unsubscribe } from '@/utils/events';
import { baseURL } from '@/utils/helpers';
import { useEffect, useState } from 'react'

const AjaxPost = () => {

    const { data, post, put, destroy, patch } = useAxios();

    const [form, setForm] = useState();
    const [key, setKey] = useState(0);

    const handleEvent = async (event: CustomEvent<{ [key: string]: any }>) => {

        const rawForm = event.detail.target
        setForm(rawForm);

        const formElement = event.detail.nativeEvent.target; // Get the form element
        const formData = new FormData(formElement); // Create a FormData object from the form
        const moreData = event.detail?.moreData || []
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

        const elementId = rawForm.id || rawForm?.closest('.modal')?.id || null

        // Specify the URL where the post request will be sent
        let url = rawForm?.getAttribute('action-url') || ''; // Get the baseUri from the event detail
        url = baseURL('api/' + url.trim('/'));

        const method: HttpVerbsType = (rawForm?.querySelector('input[name="_method"]')?.value || 'post').toLowerCase(); // Get the form's HTTP method
        const button = rawForm?.querySelector('button[type="submit"]')

        if (button) {
            button.disabled = true
            button.classList.add('disabled')

            if (button.classList.contains('btn-saving')) return
            button.classList.add('btn-saving', 'cursor-progress')

            const submitBtnLoader = button.querySelector('.submit-btn-loader')
            if (submitBtnLoader) {
                submitBtnLoader.classList.remove('d-none')
            }
        }

        let results

        // Make the request
        if (method == 'post') {
            results = await post(url, formData, { elementId });
        } else if (method == 'put') {
            results = await put(url, formData, { elementId });
        } else if (method == 'patch') {
            results = await patch(url, formData, { elementId });
        } else if (method == 'delete') {
            results = await destroy(url, formData, { elementId });
        }

        publish('ajaxPostDone', { elementId, results })
        setKey(key + 1)

        if (button) {
            button.disabled = false
            button.classList.remove('disabled')
            button.classList.remove('btn-saving', 'cursor-progress')
            const submitBtnLoader = button.querySelector('.submit-btn-loader')
            if (submitBtnLoader) {
                submitBtnLoader.classList.add('d-none')
            }

        }

    };

    useEffect(() => {

        if (data && form) {
            const modal = form?.closest('.modal')

            if (modal && !modal.classList.contains('persistent-modal')) {

                const modalToggleBtn = modal.querySelector('button[data-bs-dismiss="modal"]');

                if (modalToggleBtn) {
                    modalToggleBtn.click();
                    setForm(undefined)

                }
            }
        }

    }, [data, key])

    const eventListener = (event: CustomEvent<{ [key: string]: any }>) => {
        event.preventDefault()
        handleEvent(event);
    };

    useEffect(() => {

        unsubscribe('ajaxPost', eventListener as EventListener);
        subscribe('ajaxPost', eventListener as EventListener);

        // Cleanup the event listener when the component unmounts
        return () => {
            unsubscribe('ajaxPost', eventListener as EventListener);
        };

    }, [key]);

    return null
}

export default AjaxPost