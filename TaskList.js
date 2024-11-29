import React, { useEffect, useContext } from 'react';
import { doc, deleteDoc, getDoc, updateDoc, increment } from 'firebase/firestore';
import { db } from '../firebase';
import { UserContext } from '../contexts/UserContext';
import { toast } from 'react-toastify';

const TaskList = () => {
  const { user, setUser } = useContext(UserContext);
  const [tasks, setTasks] = useState([]);

  // Fetch tasks when component mounts
  useEffect(() => {
    const fetchTasks = async () => {
      try {
        const tasksRef = collection(db, 'tasks');
        const q = query(tasksRef, where('userId', '==', user.uid));
        const querySnapshot = await getDocs(q);
        
        const tasksList = [];
        querySnapshot.forEach((doc) => {
          tasksList.push({ id: doc.id, ...doc.data() });
        });
        
        setTasks(tasksList);
      } catch (error) {
        toast.error('Failed to fetch tasks');
      }
    };

    if (user) {
      fetchTasks();
    }
  }, [user]);

  const deleteTask = async (taskId) => {
    try {
      const taskRef = doc(db, 'tasks', taskId);
      const taskDoc = await getDoc(taskRef);
      
      if (taskDoc.exists()) {
        const task = taskDoc.data();
        const pointDeduction = -50; // Point deduction for deleting task

        // Update user points
        const userRef = doc(db, 'users', user.uid);
        await updateDoc(userRef, {
          points: increment(pointDeduction)
        });

        // Delete the task
        await deleteDoc(taskRef);
        
        // Update local state
        setTasks(prev => prev.filter(task => task.id !== taskId));
        setUser(prev => ({...prev, points: prev.points + pointDeduction}));
        toast.warning(`Task deleted. ${Math.abs(pointDeduction)} points deducted.`);
      }
    } catch (error) {
      toast.error('Failed to delete task');
    }
  };

  const checkTaskDeadline = async (task) => {
    if (task.deadline && !task.completed) {
      const deadlineDate = task.deadline.toDate();
      if (Date.now() > deadlineDate) {
        const pointDeduction = -30; // Point deduction for missing deadline
        
        try {
          const userRef = doc(db, 'users', user.uid);
          await updateDoc(userRef, {
            points: increment(pointDeduction)
          });
          
          // Update task to mark it as expired
          const taskRef = doc(db, 'tasks', task.id);
          await updateDoc(taskRef, {
            expired: true
          });
          
          setUser(prev => ({...prev, points: prev.points + pointDeduction}));
          toast.error(`Missed deadline for "${task.title}". ${Math.abs(pointDeduction)} points deducted.`);
        } catch (error) {
          console.error('Failed to deduct points for missed deadline:', error);
        }
      }
    }
  };

  useEffect(() => {
    // Check deadlines for all tasks periodically
    const interval = setInterval(() => {
      tasks.forEach(task => checkTaskDeadline(task));
    }, 60000); // Check every minute

    return () => clearInterval(interval);
  }, [tasks]);

  const toggleTaskCompletion = async (taskId) => {
    try {
      const taskRef = doc(db, 'tasks', taskId);
      const taskDoc = await getDoc(taskRef);
      
      if (taskDoc.exists()) {
        const task = taskDoc.data();
        const newCompletionStatus = !task.completed;
        const points = newCompletionStatus ? 100 : -100; // Add or remove points

        await updateDoc(taskRef, {
          completed: newCompletionStatus
        });

        // Update user points
        const userRef = doc(db, 'users', user.uid);
        await updateDoc(userRef, {
          points: increment(points),
          tasksCompleted: increment(newCompletionStatus ? 1 : -1)
        });

        // Update local state
        setTasks(prev => prev.map(t => 
          t.id === taskId ? {...t, completed: newCompletionStatus} : t
        ));
        setUser(prev => ({
          ...prev, 
          points: prev.points + points,
          tasksCompleted: prev.tasksCompleted + (newCompletionStatus ? 1 : -1)
        }));

        toast.success(newCompletionStatus 
          ? `Task completed! Earned ${points} points!` 
          : `Task uncompleted. ${Math.abs(points)} points deducted.`
        );
      }
    } catch (error) {
      toast.error('Failed to update task');
    }
  };

  return (
    <div className="task-list">
      {tasks.map(task => (
        <div key={task.id} className="task-item">
          <input
            type="checkbox"
            checked={task.completed}
            onChange={() => toggleTaskCompletion(task.id)}
          />
          <div className="task-content">
            <h3>{task.title}</h3>
            <p>{task.description}</p>
            {task.deadline && (
              <p className="deadline">
                Deadline: {task.deadline.toDate().toLocaleDateString()}
              </p>
            )}
            {task.expired && <span className="expired-tag">Expired</span>}
          </div>
          <button 
            onClick={() => deleteTask(task.id)} 
            className="delete-btn"
          >
            Delete Task
          </button>
        </div>
      ))}
    </div>
  );
};

export default TaskList;