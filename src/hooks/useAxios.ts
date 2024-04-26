import { useState } from 'react';
import axios, { AxiosRequestHeaders, AxiosResponse } from 'axios';
import { clearErrors, showErrors } from '@/utils/validation-errors';
import { useAuth } from '@/contexts/AuthContext';
import { publish } from '@/utils/events';
import { baseURL } from '@/utils/helpers';

interface AxiosResponseWithData<T> extends AxiosResponse {
    data: T;
}

const useAxios = <T = any>() => {

    axios.defaults.baseURL = baseURL('api');
    const frontendUrl = window.location.origin

    const [data, setData] = useState<T | null>(null);
    const [loading, setLoading] = useState(false);
    const [fetched, setFetched] = useState(false);
    const [errors, setErrors] = useState(null);

    // Create an Axios instance with a request interceptor
    const axiosInstance = axios.create();

    // Assuming you have a useAuth hook that manages user authentication and provides the user object with the token
    const { user, deleteUser } = useAuth();

    // Request interceptor to add Authorization header if user is authenticated
    axiosInstance.interceptors.request.use(
        (config) => {
            // Ensure config.headers is of type AxiosRequestHeaders
            if (!config.headers) {
                config.headers = {} as AxiosRequestHeaders; // Initialize as an empty object
            }

            // Add X-Frontend-URL header
            config.headers['X-Frontend-URL'] = frontendUrl;

            // Optionally add other headers here

            if (user) {
                // Add Authorization header if user is authenticated
                config.headers['Authorization'] = `Bearer ${user.token}`;
            }

            return config;
        },
        (error) => {
            return Promise.reject(error);
        }
    );

    const fetchData = async (config) => {

        let elementId: string | null = null
        if (config?.elementId) {
            elementId = config.elementId
        }

        clearErrors(elementId)

        try {
            setLoading(true);
            const response = await axiosInstance(config);

            setErrors(null, elementId);

            if (response.data?.message) {
                publish('notification', { message: response.data.message, type: 'success', status: 200 })

                if (!response.data?.results) {
                    setData(response.data.message);
                    return response.data.message
                }
            }

            setData(response.data?.results);
            return response.data?.results
        } catch (error) {

            const axiosError = error as AxiosErrorWithResponse;

            if (axios.isAxiosError(axiosError)) {

                if (axiosError.response !== undefined) {
                    const status = axiosError.response.status;

                    const msg = axiosError.response?.data?.message || 'An error occurred.'
                    const errors = axiosError.response?.data?.errors
                    setErrors(msg, elementId);

                    if (status && status !== 200 && status !== 201 && status !== 401 && (!errors || status !== 422)) {
                        publish('notification', { message: msg, type: 'error', status })
                    }

                    if (status === 401 && msg === 'Unauthenticated.') {
                        deleteUser()
                    }

                    // Handle validation errors in the response data
                    if (axiosError.response?.data.errors) {
                        showErrors(axiosError.response.data, elementId);
                    }


                } else {
                    const msg = 'We are experiencing server connection issues.'
                    setErrors(msg, elementId);
                    publish('notification', { message: msg, type: 'error', status: 0 })

                }
            } else {
                const msg = error?.message || 'An unexpected error occurred.'
                setErrors(msg, elementId);
                publish('notification', { message: msg, type: 'error', status: 0 })

            }

        } finally {
            setLoading(false);
            setFetched(true);
        }
    };

    const get = (url: string, config = {}) => fetchData({ method: 'GET', url, ...config });
    const post = (url: string, data = {}, config = {}) => fetchData({ method: 'POST', url, data, ...config });
    const put = (url: string, data = {}, config = {}) => fetchData({ method: 'POST', url, data, ...config, _method: 'patch' });
    const patch = (url: string, data = {}, config = {}) => fetchData({ method: 'POST', url, data, ...config, _method: 'patch' });
    const destroy = (url: string, data = {}, config = {}) => fetchData({ method: 'DELETE', url, data, ...config });
    const getFile = async (url: string, config = {}, placeholder: string | null = null) => {

        if (!url && placeholder) return placeholder

        try {
            setLoading(true);
            const response = await axiosInstance({
                method: 'GET',
                url: `/admin/file-repo/${url}`,
                responseType: 'blob', // This tells Axios to handle the response as a binary blob
                ...config,
            });

            const file = new Blob([response.data], { type: response.headers['content-type'] });
            return file;
        } catch (error) {
            // Error handling code...
        } finally {
            setLoading(false);
            setFetched(true);
        }

        return ''

    };

    return { data, loading, fetched, errors, get, post, put, patch, destroy, getFile };
};

export default useAxios;
