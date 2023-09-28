import { FormEventHandler, useState } from "react";
import DefaultLayout from "@/Layouts/DefaultLayout";
import { useForm } from "@inertiajs/react";

const Create = () => {

    const { data, setData, post, processing, errors, reset } = useForm({
        title: '',
        content_short: '',
        content: '',
        status: 'published',
    });


    const submit: FormEventHandler = (e) => {
        e.preventDefault();
        post(route('posts.store'));
    };

    return (
        <DefaultLayout>
            <div>
                <div className="rounded-sm border border-stroke bg-white shadow-default dark:border-strokedark dark:bg-boxdark">
                    <div className="border-b border-stroke px-6.5 py-4 dark:border-strokedark">
                        <h3 className="font-medium text-black dark:text-white">Add a new Post</h3>
                    </div>
                    <form onSubmit={submit}>
                        <div className="p-6.5">
                            <div className="mb-4.5 form-group">
                                <label className="mb-2.5 block text-black dark:text-white">Title</label>
                                <input
                                    value={data.title} onChange={(e) => setData('title', e.target.value)}
                                    name="title" type="text" placeholder="Enter title" className="w-full rounded border-[1.5px] border-stroke bg-transparent px-5 py-3 font-medium outline-none transition focus:border-primary active:border-primary disabled:cursor-default disabled:bg-whiter dark:border-form-strokedark dark:bg-form-input dark:focus:border-primary form-control" />
                            </div>
                            <div className="mb-4.5 form-group">
                                <label className="mb-2.5 block text-black dark:text-white">Summary</label>
                                <textarea
                                    value={data.content_short} onChange={(e) => setData('content_short', e.target.value)}
                                    name="content_short" placeholder="Content Summary" className="w-full rounded border-[1.5px] border-stroke bg-transparent px-5 py-3 font-medium outline-none transition focus:border-primary active:border-primary disabled:cursor-default disabled:bg-whiter dark:border-form-strokedark dark:bg-form-input dark:focus:border-primary form-control" />
                            </div>
                            <div className="mb-4.5 form-group">
                                <label className="mb-2.5 block text-black dark:text-white">Content</label>
                                <textarea
                                    value={data.content} onChange={(e) => setData('content', e.target.value)}
                                    name="content" placeholder="Enter Content" rows={7} className="w-full rounded border-[1.5px] border-stroke bg-transparent px-5 py-3 font-medium outline-none transition focus:border-primary active:border-primary disabled:cursor-default disabled:bg-whiter dark:border-form-strokedark dark:bg-form-input dark:focus:border-primary form-control" />
                            </div>
                            <div className="mb-4.5 form-group">
                                <label className="mb-2.5 block text-black dark:text-white">Status</label>
                                <select
                                    value={data.status} onChange={(e) => setData('status', e.target.value)}
                                    name="status" className="appearance-none w-full py-1 px-2 bg-white">
                                    <option value="published">Published</option>
                                    <option value="draft">Draft</option>
                                </select>
                            </div>
                            <button className="flex w-full justify-center rounded bg-primary p-3 font-medium text-gray">Save</button>
                        </div>
                    </form>
                </div>
            </div>
        </DefaultLayout>
    );
};

export default Create;
