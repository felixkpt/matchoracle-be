import { useEffect, useRef, useState } from 'react';
import { Editor } from '@tinymce/tinymce-react';
import { Icon } from '@iconify/react/dist/iconify.js';
import { useNavigate, useParams } from 'react-router-dom';
import useAxios from '@/hooks/useAxios';
import { publish, subscribe, unsubscribe } from '@/utils/events';
import Dropzone from '@/components/Dropzone';
import PageHeader from '@/components/PageHeader';
import { baseURL, tnymce_key } from '@/utils/helpers';
import Select from "react-select";
import useQueryParams from '@/hooks/useQueryParams';
import { toggleSidebar } from '@/Layouts/Authenicated/SideNav/Index';
import Settings from './Includes/Settings';

const CreateOrUpdate = () => {

  const { id } = useParams()
  const queryParams = useQueryParams();

  const [customers, setCustomers] = useState([]);
  const [topics, setTopics] = useState([]);
  const [selectedCategory, setSelectedCategory] = useState(null);
  const [selectedTopic, setSelectedTopic] = useState(null);

  const { get } = useAxios();

  useEffect(() => {
    fetchSelectData(`admin/posts/categories?all=1&id=${queryParams.get('category_id') || '0'}`, setCustomers);
  }, []);

  const fetchSelectData = (url: string, setDataFunction: (data: []) => any) => {
    get(url).then((results) => {
      if (results)
        setDataFunction(results.data);
    });
  };

  const handleSelectChange = async (job: { uri: string, fn: (data: []) => any }, dependencies: object[]) => {
    let updatedUri = job.uri;

    dependencies.forEach((dependency: any) => {

      const [name, selectedValue] = dependency

      updatedUri += `&${name}=${selectedValue?.id || 0}`;

    });

    fetchSelectData(updatedUri, job.fn);

  };

  const navigate = useNavigate()

  const [key, setKey] = useState(0)
  const [record, setRecord] = useState(null)

  const [title, setTitle] = useState('')
  const [contentShort, setContentShort] = useState('')
  const [content, setContent] = useState('')
  const [initialContent, setInitialContent] = useState('')
  const [status_id, setStatusId] = useState('published')
  const [statuses, setStatuses] = useState([])

  const [files, setFiles] = useState([]);

  const editorRef = useRef(null);

  const { get: getDoc } = useAxios()
  const { post, errors } = useAxios()

  useEffect(() => {

    if (id) {
      getDoc(`admin/posts/view/${id}`).then(res => {

        if (res) {
          const { data, statuses } = res
          setRecord(data)
          setTitle(data.title)
          setContentShort(data.content_short)
          setInitialContent(data.content)
          setStatusId(data.status_id)

          setStatuses(statuses)

        }
      })

    }

  }, [id])

  useEffect(() => {
    if (!id) {

      getDoc('/admin/settings/picklists/statuses/post?all=1').then((res) => {

        if (res) {
          setStatuses(res)

          setKey((curr) => curr + 1)
          setRecord(null);
          setTitle('');
          setContentShort('');
          setContent('');
          setStatusId(res.find((status: any) => status.name === 'published').id || 0);

        }
      })
    }

  }, [id]);

  useEffect(() => {

    const sidebarWasClosedInitially = document.body.classList.contains('sb-sidenav-toggled')
    toggleSidebar(undefined, 'hide', true)

    const handleAjaxPostDone = (event: Event) => {
      if (event?.detail) {
        const { elementId, results } = event.detail;

        if (elementId === 'posts-form' && results) {
          navigate('/admin/posts/view/' + results.id + '/edit');
        }
      }
    };

    subscribe('ajaxPostDone', handleAjaxPostDone)

    return () => {
      unsubscribe('ajaxPostDone', handleAjaxPostDone)

      if (sidebarWasClosedInitially === false)
        toggleSidebar(undefined, 'hide')
    }

  }, [])

  return (
    <div>
      <PageHeader title={`${id ? 'Edit Post #' + id : 'Create Post'}`} listUrl='/admin/posts' />
      <div className='card'>
        <div className="card-body">
          <form key={key} id={`posts-form`} onSubmit={(e) => publish('ajaxPost', e, { image: files[0] })}
            action-url={
              record
                ? `/admin/posts/view/${record.id}`
                : 'admin/posts'
            }
            encType='multipart/form-data'
          >
            <div className='row'>

              <div className="col-12">
                {record && statuses && <input type="hidden" value="put" name="_method" />}
                <div className='d-flex justify-content-end mb-3'>
                  <div className="btn-group" role="group" aria-label="Button group with nested dropdown">
                    <button type="submit" className="btn btn-primary">Publish</button>
                    <div className="btn-group" role="group">
                      <button id="btnGroupDrop1" type="button" className="btn btn-dark dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                        More
                      </button>
                      <ul className="dropdown-menu" aria-labelledby="btnGroupDrop1">
                        {statuses.map(status => (
                          <li key={status.id}><button type="submit" onClick={() => setStatusId(status.id)} className="dropdown-item btn btn-warning d-flex align-items-center gap-1"><Icon icon={`bi:bookmark`} />Save as {status.name}</button></li>
                        ))}
                      </ul>
                      <button class="btn btn-primary" type="button" data-bs-toggle="collapse" data-bs-target="#collapseExample" aria-expanded="false" aria-controls="collapseExample">
                        Button with data-bs-target
                      </button>
                    </div>
                  </div>
                  <input type="hidden" name='status_id' defaultValue={status_id} />
                </div>
              </div>
              
              <div className="col">

                <div className='form-group mb-3'>
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
                      options={customers}
                      getOptionValue={(option: any) => option && `${option?.id}`}
                      getOptionLabel={(option: any) => option && `${option?.name}`}
                      name='category_id'
                    />
                  </div>
                </div>

                <div className='form-group mb-3'>
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

                <div className="form-group mb-3">
                  <label htmlFor="title">Title</label>
                  <input type="text" id="title" name="title" defaultValue={title} className='form-control' />
                </div>
                <div className="form-group mb-4">
                  <label htmlFor="content_short">Content short</label>
                  <input type="text" id="content_short" name="content_short" defaultValue={contentShort} className='form-control' />
                </div>
                <div className="form-group mb-3">
                  <div className='form-control'>
                    <Editor
                      apiKey={tnymce_key}
                      onInit={(evt, editor) => editorRef.current = editor}
                      initialValue={initialContent}
                      init={{
                        height: 500,
                        menu: {
                          file: { title: 'File', items: 'newdocument restoredraft | preview | print ' },
                          edit: { title: 'Edit', items: 'undo redo | cut copy paste | selectall | searchreplace' },
                          view: { title: 'View', items: 'code | visualaid visualchars visualblocks | spellchecker | preview fullscreen' },
                          insert: { title: 'Insert', items: 'image link media template codesample inserttable | charmap emoticons hr | pagebreak nonbreaking anchor toc | insertdatetime' },
                          format: { title: 'Format', items: 'bold italic underline strikethrough superscript subscript codeformat | formats blockformats fontformats fontsizes align lineheight | forecolor backcolor | removeformat' },
                          tools: { title: 'Tools', items: 'spellchecker spellcheckerlanguage | code wordcount' },
                          table: { title: 'Table', items: 'inserttable | cell row column | tableprops deletetable' },
                          help: { title: 'Help', items: 'help' }
                        },

                        plugins: [
                          'image', 'codesample'
                        ],

                        toolbar: 'undo redo | formatselect | ' +
                          'bold italic backcolor | alignleft aligncenter ' +
                          'alignright alignjustify | bullist numlist outdent indent | ' +
                          'removeformat | help codesample',
                        content_style: 'body { font-family:Helvetica,Arial,sans-serif; font-size:14px }',

                        /* enable title field in the Image dialog*/
                        image_title: true,
                        /* enable automatic uploads of images represented by blob or data URIs*/
                        automatic_uploads: true,
                        file_picker_types: 'image',
                        images_upload_credentials: true,
                        /* and here's our custom image picker*/
                        file_picker_callback: function (cb, value, meta) {
                          var input = document.createElement('input');
                          input.setAttribute('type', 'file');
                          input.setAttribute('accept', 'image/*');

                          input.onchange = function () {
                            var file = this.files[0];

                            // Create a FormData object to send the file to the server
                            var formData = new FormData();
                            formData.append('image', file);
                            formData.append('files_folder', 'documentation');

                            post('/admin/file-repo/upload-image', formData).then((results) => {

                              if (results) {
                                const { data, token } = results

                                cb(baseURL('/admin/file-repo/' + data.path + '?token=' + token), { title: data.caption, class: 'asas' });
                              }

                            })

                          };

                          input.click();
                        },
                        image_class_list: [
                          { title: 'Autofetch Image', value: 'autofetch-image' },
                        ]
                      }}
                      onChange={(e) => setContent(e.target.getContent())}
                    />
                    <textarea defaultValue={content} name="content" className='d-none'></textarea>
                  </div>
                </div>

                <div className="form-group mb-3 col-6 ms-auto">
                  <div className='form-control' id='image' >
                    <Dropzone files={files} setFiles={setFiles} fileType='featured image' maxFiles={1} />
                  </div>
                </div>
              </div>
              <div class="col-3 col-xl-2 collapse" id="collapseExample">
                <Settings />
              </div>
            </div>
          </form>
        </div>
      </div>
    </div>
  );
}


export default CreateOrUpdate