import { useState, useEffect, useCallback } from 'react';
import { getPost, createPost, deletePost } from '../services/PostService';

const usePosts = ({ userId = null, postId = null, tag = null }) => {
  const [posts, setPosts] = useState([]);
  const [error, setError] = useState(null);
  const [loading, setLoading] = useState(false);

  const fetchPosts = useCallback(async (userId, postId, tag) => {
    try {
      setLoading(true);
      const response = await getPost(userId, postId, tag);
      if (response.data.success) {
        setPosts(response.data.data);
      } else {
        setError(response.error || 'Failed to fetch posts');
      }
    } catch (err) {
      setError('Failed to fetch posts');
      console.error(err);
    } finally {
      setLoading(false);
    }
  }, []);

  useEffect(() => {
    if (userId || postId || tag) {
      fetchPosts(userId, postId, tag);
    }
  }, [userId, postId, tag, fetchPosts]);

  const handleCreatePost = async (newPost) => {
    try {
      setLoading(true);
      const response = await createPost(
        newPost.userId,
        newPost.name,
        newPost.content,
        newPost.tag,
        newPost.base64
      );
      if (response.data.success) {
       
        setPosts((prevPosts) => [response.data.data, ...prevPosts]);
      } else {
        setError(response.data.error || 'Failed to create post');
      }
    } catch (err) {
      setError('Failed to create post');
      console.error(err);
    } finally {
      setLoading(false);
    }
  };

  const handleDeletePost = async (postIdToDelete) => {
    try {
      setLoading(true);
      // 假設 deletePost 服務需要 userId，請確保它可用。
      // 如果您的 hook 總是使用特定的 userId 實例化，則可以使用該 userId。
      // 否則，您可能需要將其傳遞給 handleDeletePost 或確保它在作用域內。
      const response = await deletePost(userId, postIdToDelete); 
      if (response.data.success) {
        setPosts((prevPosts) => prevPosts.filter(post => post.id !== postIdToDelete));
      } else {
        setError(response.data.error || 'Failed to delete post');
      }
    } catch (err) {
      setError('Failed to delete post');
      console.error(err);
    } finally {
      setLoading(false);
    }
  };

  return { posts, error, loading, fetchPosts, handleCreatePost, handleDeletePost};
};

export default usePosts;