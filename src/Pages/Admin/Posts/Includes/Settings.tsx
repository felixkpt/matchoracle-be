import Select from "react-select";
import useQueryParams from '@/hooks/useQueryParams';
import useAxios from "@/hooks/useAxios";
import { useEffect, useState } from "react";
import Dropzone from '@/components/Dropzone';
import { Icon } from "@iconify/react/dist/iconify.js";
import Str from "@/utils/Str";
import { PostInterface } from "@/interfaces/PostInterfaces";
import useListDependsOn from "@/hooks/useListDependsOn";

interface Props {
    post: PostInterface
    files: any
    setFiles: any
    statuses: any
}

const Settings = ({ post, files, setFiles, statuses }: Props) => {

    const queryParams = useQueryParams();

    const [categories, setCategories] = useState([]);
    const [topics, setTopics] = useState([]);
    const [selectedCategory, setSelectedCategory] = useState(null);
    const [selectedTopic, setSelectedTopic] = useState(null);

    const [contentShort, setContentShort] = useState('')
    const [status_id, setStatusId] = useState()

    const { fetchSelectData, handleSelectChange } = useListDependsOn()

    useEffect(() => {
        fetchSelectData(`admin/posts/categories?all=1&id=${queryParams.get('category_id') || '0'}`, setCategories);
    }, []);

    useEffect(() => {

        if (post) {
            setContentShort(post.content_short);
            setStatusId(post.status_id)

            if (post?.category) {
                setSelectedCategory(post?.category)
            }

            if (post?.topic) {
                setSelectedTopic(post?.topic)
            }

        }

        if (statuses && statuses.length > 0) {
            setStatusId(statuses.find((status: any) => status.name === 'published').id || 0);
        }
    }, [post])

    return (
        <div>
            <div className="accordion" id="postEditorAccordion">
                <div className="accordion-item mb-2">
                    <h2 className="accordion-header" id="headingTwo">
                        <button className="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapseTwo" aria-expanded="true" aria-controls="collapseTwo">
                            Category & Tagging
                        </button>
                    </h2>
                    <div id="collapseTwo" className="accordion-collapse collapse show" aria-labelledby="headingTwo" data-bs-parent="#postEditorAccordion">
                        <div className="accordion-body">
                            <div className='form-group mb-4 inside-accordion'>
                                <label htmlFor="category_id">Category</label>
                                <div className='form-control' id='category_id'>
                                    <Select
                                        value={selectedCategory}
                                        onChange={(newValue) => {
                                            setSelectedCategory(newValue)
                                            handleSelectChange(
                                                { uri: `admin/posts/categories/topics?all=1`, fn: setTopics },
                                                [
                                                    ['category_id', newValue, setTopics],
                                                ],
                                            )
                                        }
                                        }
                                        options={categories}
                                        getOptionValue={(option: any) => option && `${option?.id}`}
                                        getOptionLabel={(option: any) => option && `${option?.name}`}
                                        name='category_id'
                                    />
                                </div>
                            </div>

                            <div className='form-group mb-4 inside-accordion'>
                                <label htmlFor="topic_id">Topic</label>
                                <div className='form-control' id='topic_id'>
                                    <Select
                                        value={selectedTopic}
                                        onChange={(newValue) => {
                                            setSelectedTopic(newValue)
                                        }
                                        }
                                        options={topics}
                                        getOptionValue={(option: any) => option && `${option?.id}`}
                                        getOptionLabel={(option: any) => option && `${option?.name}`}
                                        name='topic_id'
                                    />
                                </div>
                            </div>
                            <div className='form-group mb-4 inside-accordion'>
                                <label htmlFor="priority_number">Priority number</label>
                                <input type="number" name="priority_number" className="form-control" id="priority_number" defaultValue={post?.priority_number ?? 9999} />
                            </div>
                        </div>
                    </div>
                </div>
                <div className="accordion-item mb-2">
                    <h2 className="accordion-header" id="headingOne">
                        <button className="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseOne" aria-expanded="false" aria-controls="collapseOne">
                            Post status
                        </button>
                    </h2>
                    <div id="collapseOne" className="accordion-collapse collapse" aria-labelledby="headingOne" data-bs-parent="#postEditorAccordion">
                        <div className="accordion-body">
                            <div className="form-group mb-4 inside-accordion">
                                <div className="form-control">
                                    <ul className="list-unstyled" aria-labelledby="btnGroupDrop1">
                                        {statuses && status_id && statuses.map((status: any) => (
                                            <li key={status.id} className="mb-2 shadow-sm p-1 rounded">
                                                <div className="form-check">
                                                    <input
                                                        className="form-check-input"
                                                        id={`${status.id}_status`}
                                                        name="status"
                                                        value={status_id}
                                                        type='radio'
                                                        onChange={() => setStatusId(status.id)}
                                                        checked={status.id === status_id}
                                                    />
                                                    <label className="form-check-label d-flex gap-2 align-items-center" htmlFor={`${status.id}_status`}>
                                                        <Icon icon={`${status.icon}`} className={`${status.class}`} />{Str.title(status.name)}
                                                    </label>
                                                </div>
                                            </li>
                                        ))}
                                    </ul>
                                    <input type="hidden" name='status_id' defaultValue={status_id} />
                                </div>

                            </div>
                        </div>
                    </div>
                </div>
                <div className="accordion-item mb-2">
                    <h2 className="accordion-header" id="headingThree">
                        <button className="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseThree" aria-expanded="false" aria-controls="collapseThree">
                            Excerpt/Description
                        </button>
                    </h2>
                    <div id="collapseThree" className="accordion-collapse collapse" aria-labelledby="headingThree" data-bs-parent="#postEditorAccordion">
                        <div className="accordion-body">
                            <div className="form-group mb-4 inside-accordion">
                                <label htmlFor="content_short">Content short</label>
                                <textarea rows={5} id="content_short" name="content_short" defaultValue={contentShort} className='form-control' />
                            </div>
                        </div>
                    </div>
                </div>
                <div className="accordion-item mb-2">
                    <h2 className="accordion-header" id="heading4">
                        <button className="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse4" aria-expanded="false" aria-controls="collapse4">
                            Featured Image
                        </button>
                    </h2>
                    <div id="collapse4" className="accordion-collapse collapse" aria-labelledby="heading4" data-bs-parent="#postEditorAccordion">
                        <div className="accordion-body">
                            <div className="form-group mb-4 inside-accordion">
                                <div className='form-control' id='image'>
                                    <Dropzone files={files} setFiles={setFiles} fileType='featured image' maxFiles={1} />
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    )
}

export default Settings