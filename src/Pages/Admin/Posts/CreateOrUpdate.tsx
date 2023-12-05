import { useEffect, useRef, useState } from 'react';
import { Editor } from '@tinymce/tinymce-react';
import { Icon } from '@iconify/react/dist/iconify.js';
import { useNavigate, useParams } from 'react-router-dom';
import useAxios from '@/hooks/useAxios';
import { publish, subscribe, unsubscribe } from '@/utils/events';
import PageHeader from '@/components/PageHeader';
import { baseURL, tnymce_key } from '@/utils/helpers';
import { toggleSidebar } from '@/Layouts/Authenicated/SideNav/Index';
import Settings from './Includes/Settings';
import { PostInterface } from '@/interfaces/PostInterfaces';

const CreateOrUpdate = () => {

  const { id } = useParams()

  const navigate = useNavigate()

  const [key, setKey] = useState(0)
  const [post, setPost] = useState<PostInterface | undefined>()

  const [title, setTitle] = useState('')
  const [content, setContent] = useState('')
  const [initialContent, setInitialContent] = useState('')
  const [statuses, setStatuses] = useState([])

  const [files, setFiles] = useState<string[]>([]);

  const editorRef = useRef<any>(null);

  const { get: getPost } = useAxios()
  const { post: doPost, errors } = useAxios()

  useEffect(() => {

    if (id) {
      getPost(`admin/posts/view/${id}`).then(res => {

        if (res) {
          const { data, statuses } = res
          setPost(data)
          setStatuses(statuses)
        }

      })

    }

  }, [id])

  useEffect(() => {

    if (post) {

      setTitle(post.title)
      setContent(post.content)
      setInitialContent(post.content)
      let f = post.image
      setFiles([f]);

    }
    else if (!id) {

      getPost('/admin/settings/picklists/statuses/post?all=1').then((res) => {

        if (res) {
          setStatuses(res)

          setKey((curr) => curr + 1)
          setPost(undefined);
          setTitle('');
          setContent('');
          setFiles([]);

        }
      })
    }
  }, [post, id])


  useEffect(() => {

    const sidebarWasClosedInitially = document.body.classList.contains('sb-sidenav-toggled')
    toggleSidebar(undefined, 'hide', true)

    const handleAjaxPostDone = (event: any) => {
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

  useEffect(() => {

    const postEditorNavToggler = document.querySelector('#postEditorNavToggler')
    if (postEditorNavToggler) {
      postEditorNavToggler.addEventListener('click', function () {

        const postEditorWrapper = document.querySelector('#postEditorWrapper')
        if (postEditorWrapper) {
          postEditorWrapper.classList.toggle('collapsed')
        }

      })

    }

  }, [post, statuses])

  return (
    <div>
      <PageHeader title={`${id ? 'Edit Post #' + id : 'Create Post'}`} listUrl='/admin/posts' />
      <div className='card'>
        <div className="card-body">
          <form key={key} id={`posts-form`} onSubmit={(e) => publish('ajaxPost', e, { image: files[0] })}
            action-url={
              post
                ? `/admin/posts/view/${post.id}`
                : 'admin/posts'
            }
            encType='multipart/form-data'
          >
            <div className='row'>

              <div className="col-12">
                {post && statuses && <input type="hidden" value="put" name="_method" />}
                <div className='d-flex justify-content-end mb-3'>
                  <div className="btn-group" role="group" aria-label="Button group with nested dropdown">
                    <button type="submit" className="btn btn-primary">Save Post</button>
                    <div className="btn-group" role="group">
                      <button className="btn btn-secondary" id="postEditorNavToggler" type="button" >
                        Settings <Icon icon={'pepicons-pop:dots-y'} />
                      </button>
                    </div>
                  </div>
                </div>
              </div>
            </div>
            <div id='postEditorWrapper'>

              <div className='main-content p-2 rounded'>

                <div className="form-group mb-3">
                  <label htmlFor="title">Title</label>
                  <input type="text" id="title" name="title" defaultValue={title} className='form-control shadow' />
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

                            doPost('/admin/file-repo/upload-image', formData).then((results) => {

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
              </div>
              <div className="side-nav p-2 shadow rounded">
                <Settings post={post} files={files} statuses={statuses} setFiles={setFiles} />
              </div>
            </div>
          </form>
        </div>
      </div>
    </div>
  );
}


export default CreateOrUpdate