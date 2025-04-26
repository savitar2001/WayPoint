import React, { useState } from 'react';
import { useSelector } from 'react-redux';
import Header from '../../components/Header/Header.js';
import FileUploader from '../../components/FileUploader/FileUploader';
import Tag from '../../components/Tag/Tag';
import Textarea from '../../components/TextArea/TextArea';
import usePosts from '../../hooks/UsePosts';
import './CreatePostPage.css';

const CreatePostPage = () => {
  const { userId, userName } = useSelector((state) => ({
    userId: state.auth.userId,
    userName: state.auth.userName,
  }));

  const { handleCreatePost, loading, error } = usePosts({});
  const [selectedFile, setSelectedFile] = useState(null);
  const [tags, setTags] = useState([]);
  const [content, setContent] = useState('');

  const handleFileUpload = (files) => {
    if (files.length > 0) {
      const reader = new FileReader();
      reader.onload = (e) => {
        setSelectedFile(e.target.result); // 將圖片轉為 base64
      };
      reader.readAsDataURL(files[0]);
    }
  };

  const handleAddTag = (newTags) => {
    setTags(newTags);
  };

  const handleSubmit = async () => {
    if (!content.trim() || !selectedFile) {
      alert('Please provide content and an image.');
      return;
    }

    // 確保 tags 是字符串數組
    const formattedTags = tags.map(tag => tag.trim());


    const newPost = {
      userId,
      name: userName,
      content,
      tag:formattedTags,
      base64: selectedFile,
    };

    await handleCreatePost(newPost);
    if (!error) {
      alert('Post created successfully!');
      setContent('');
      setTags([]);
      setSelectedFile(null);
    }
  };

  return (
    <div className="create-post-page">
      <Header />
      <h1>Create a New Post</h1>
      <FileUploader onFileUpload={handleFileUpload} />
      <Tag onTagsChange={handleAddTag} />
      <Textarea
        placeholder="Write your post here..."
        maxLength={200}
        onChange={(e) => setContent(e.target.value)}
        value={content}
      />
      <button
        className="submit-button"
        onClick={handleSubmit}
        disabled={loading}
      >
        {loading ? 'Posting...' : 'Post'}
      </button>
      {error && <p className="error-message">{error}</p>}
    </div>
  );
};

export default CreatePostPage;