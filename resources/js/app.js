import { createApp } from 'vue';

import StudentManager from './components/StudentsComponent.vue';
import SubjectManager from './components/SubjectsComponent.vue';
import EnrollmentManager from './components/EnrollmentsComponent.vue';
import GradeManager from './components/GradesComponent.vue';

const app = createApp({});

app.component('student-manager', StudentManager);
app.component('subject-manager', SubjectManager);
app.component('enrollment-manager', EnrollmentManager);
app.component('grade-manager', GradeManager);

app.mount('#app');