import { useState } from 'react';
import axios, { AxiosResponse } from 'axios';
import { useAuth } from '@/contexts/AuthContext';
import { baseURL } from '@/utils/helpers';

axios.defaults.baseURL = baseURL('');

interface AxiosResponseWithData<T> extends AxiosResponse {
    data: T;
}

const useLoadAssets = <T = any>() => {
    const [loading, setLoading] = useState(false);
    const [errors, setErrors] = useState(null);

    // Create an Axios instance with a request interceptor
    const axiosInstance = axios.create();

    // Assuming you have a useAuth hook that manages user authentication and provides the user object with the token
    const { user } = useAuth();

    // Request interceptor to add Authorization header if user is authenticated
    axiosInstance.interceptors.request.use(
        (config) => {
            if (user) {
                config.headers = {
                    ...config.headers,
                    Authorization: `Bearer ${user.token}`,
                };
            }
            return config;
        },
        (error) => {
            return Promise.reject(error);
        }
    );

    const loadImage = async (url: string, config = {}) => {

        try {
            setLoading(true);
            const response = await axiosInstance({
                method: 'GET',
                url: `/admin/file-repo/${url}`,
                responseType: 'blob', // This tells Axios to handle the response as a binary blob
                ...config,
            });

            const file = new Blob([response.data], { type: response.headers['content-type'] });

            const imageSrc = URL.createObjectURL(file);

            return imageSrc;

        } catch (error) {
            return ''
        } finally {
            setLoading(false);
        }
    };


    return { loadImage, loading, errors };
};

export default useLoadAssets;
