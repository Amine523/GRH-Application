<script>
import { DatePickerComponent, TimePickerComponent } from '@syncfusion/ej2-vue-calendars'
import { RadioButtonComponent } from '@syncfusion/ej2-vue-buttons'
import { SliderComponent } from '@syncfusion/ej2-vue-inputs'
import { ScheduleComponent, Day, Month, Agenda } from '@syncfusion/ej2-vue-schedule'
import { DropDownListComponent } from '@syncfusion/ej2-vue-dropdowns'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { Head, router, useForm, usePage } from '@inertiajs/vue3'
import { useToast } from 'vue-toastification'
import Dialog from 'primevue/dialog'
import PrimaryButton from '@/Components/PrimaryButton.vue'
import DataTable from 'primevue/datatable'
import Column from 'primevue/column'
import InputText from 'primevue/inputtext'
import Tag from 'primevue/tag'
import moment from 'moment'
import RadioButton from 'primevue/radiobutton'
import Fieldset from 'primevue/fieldset'
import AutoComplete from 'primevue/autocomplete'
import Select from 'primevue/select'
import DatePicker from 'primevue/datepicker'
import Slider from 'primevue/slider'
import InputNumber from 'primevue/inputnumber'
import Loading from 'vue-loading-overlay'
import 'vue-loading-overlay/dist/css/index.css'
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import InputError from '@/Components/InputError.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';

export default {
    name: 'Index',
    components: {
        PrimaryButton,
        'ejs-schedule': ScheduleComponent,
        'ejs-datepicker': DatePickerComponent,
        'ejs-timepicker': TimePickerComponent,
        'ejs-radiobutton': RadioButtonComponent,
        'ejs-slider': SliderComponent,
        'ejs-dropdownlist': DropDownListComponent,
        Head,
        AuthenticatedLayout,
        DataTable,
        Column,
        InputText,
        Tag,
        Dialog,
        RadioButton,
        Fieldset,
        AutoComplete,
        Select,
        DatePicker,
        Slider,
        InputNumber,
        Loading,
        InputLabel,
        TextInput,
        InputError,
        SecondaryButton
    },
    provide: {
        schedule: [Day, Month, Agenda]
    },
    data() {
        const toast = useToast();
        return {
            page: usePage(),
            toast,
            selectedLeaves: [],
            overlapWarning: null,
            leaveTypes: [
                { label: 'Vacation', value: 'vacation' },
                { label: 'Sick', value: 'sick' },
                { label: 'Authorisation', value: 'authorisation' },
                { label: 'Half Day', value: 'halfday' },
                { label: 'Late Deduction', value: 'deduction' }
            ],
            // Jours fériés définis par mois et jour (sans année)
            officialHolidays: [
                { month: 0, day: 1, name: "Jour de l'An" },
                { month: 2, day: 20, name: "Fête de l'Indépendance" },
                { month: 3, day: 9, name: "Fête des Martyrs" }, 
                { month: 4, day: 1, name: "Fête du Travail" }, 
                { month: 6, day: 25, name: "Fête de la République" },
                { month: 7, day: 13, name: "Fête de la Femme" }, 
                { month: 9, day: 15, name: "Fête de l'Évacuation" }, 
                { month: 11, day: 17, name: "Fête de la Révolution" }
                
            ],
            sessionOptions: [
                { label: 'Morning (08:00 - 12:00)', value: 'morning' },
                { label: 'Afternoon (13:00 - 17:00)', value: 'afternoon' }
            ],
            timeOptions: [
                { label: '08:00', value: '08:00' },
                { label: '08:30', value: '08:30' },
                { label: '09:00', value: '09:00' },
                { label: '09:30', value: '09:30' },
                { label: '10:00', value: '10:00' },
                { label: '10:30', value: '10:30' },
                { label: '11:00', value: '11:00' },
                { label: '11:30', value: '11:30' },
                { label: '12:00', value: '12:00' },
                { label: '12:30', value: '12:30' },
                { label: '13:00', value: '13:00' },
                { label: '13:30', value: '13:30' },
                { label: '14:00', value: '14:00' },
                { label: '14:30', value: '14:30' },
                { label: '15:00', value: '15:00' },
                { label: '15:30', value: '15:30' },
                { label: '16:00', value: '16:00' },
                { label: '16:30', value: '16:30' }
            ],
            // Schedule settings
            eventSettings: {
                dataSource: [],
                allowAdding: false
            },
            workDays: [1, 2, 3, 4, 5],
            views: ['Month', 'Day', 'Agenda'],
            selectedDate: new Date(),
            activeDialog: null, 
            showHistoryDialog: false,
            showDialog: false,
            showRefuseDialog: false,  

            // User and team data
            users: [],
            mappedUsers: [],
            selectedProducts: [],
            localLeaves: [],

            // Table filters
           
              /*  global: { value: '', matchMode: 'contains' }*/
            // zedt7a 
               filters: {
            global: { value: null }
        },
        currentPage: 1,
        perPage: 10,

            // Leave form data using Inertia's useForm()
            leaveForm: useForm({
                type_of_leave: '',
                start_day: null,
                start_time: new Date('1970-01-01T08:00:00'),
                end_day: null,
                session: null,
                authorisation_hour: 0.5,
                reason: '',
                team_user: null,
                user_id: null,
                deduction_days: null,
            }),

            // Leave action variables
            refusedLeave: null,
            refuseReason: null,
            processingLeaveRequest: false,
            isAddHolidayDialogOpen: false,
            holidayForm: useForm({
                name: '',
                start_date: '',
                end_date: '',
            }),
            hasOverlap: false,
        isCheckingOverlap: false,
        isCheckingOverlap: false,
        isButtonDisabled: false,
         
        }
    },
    props: {
        leaves: Array,
        user: Object,
        holidays: Array
    },
    computed: {
        // Add to computed properties
isHolidayDate() {
    return (date) => {
        const [year, month, day] = date.split('-').map(Number);
        const targetDate = new Date(year, month - 1, day);
        return this.holidays.some(holiday => {
            const startDate = new Date(holiday.start_date);
            const endDate = new Date(holiday.end_date);
            return targetDate >= startDate && targetDate <= endDate;
        });
    };
  
},
disabledHolidayDates() {
    const disabledDates = [];
    this.holidays.forEach(holiday => {
        const start = new Date(holiday.start_date);
        const end = new Date(holiday.end_date);
        let current = new Date(start);

        while (current <= end) {
            disabledDates.push(new Date(current));
            current.setDate(current.getDate() + 1);
        }
    });
    return disabledDates;
},
disabledDates() {
    const dates = [];
    const currentYear = new Date().getFullYear();
    
    // Ajouter les jours fériés de la base de données
    if (Array.isArray(this.holidays)) {
        this.holidays.forEach(holiday => {
            const start = new Date(holiday.start_date);
            const end = new Date(holiday.end_date);
            let current = new Date(start);
            
            while (current <= end) {
                dates.push(new Date(current));
                current.setDate(current.getDate() + 1);
            }
        });
    }
    
    // Ajouter les jours fériés statiques
    const officialHolidays = [
        { month: 0, day: 1 },    // Jour de l'An
        { month: 2, day: 20 },   // Fête de l'Indépendance
        { month: 3, day: 9 },    // Fête des Martyrs
        { month: 4, day: 1 },    // Fête du Travail
        { month: 6, day: 25 },   // Fête de la République
        { month: 7, day: 13 },   // Fête de la Femme
        { month: 9, day: 15 },   // Fête de l'Évacuation
        { month: 11, day: 17 }   // Fête de la Révolution
    ];
    
    // Ajouter pour l'année en cours et la suivante
    [currentYear, currentYear + 1].forEach(year => {
        officialHolidays.forEach(holiday => {
            const date = new Date(year, holiday.month, holiday.day);
            // Vérifier si la date n'est pas déjà incluse
            if (!dates.some(d => d.getTime() === date.getTime())) {
                dates.push(date);
            }
        });
    });
    
    return dates;
},
        filteredLeaveTypes () {
            return this.leaveTypes.filter(type => type.value !== 'deduction' || this.isAdmin)
        },
        approvedLeaves () {
            const events = []
            this.users = this.$attrs.users || []
            this.mappedUsers = this.users?.length ? this.mapToOptions(this.users, ['profile.first_name', 'profile.last_name'], 'id') : []
            
            // Add approved leaves
            const leaves = Array.isArray(this.localLeaves) ? this.localLeaves : [];
            leaves
                .filter(leave => leave?.status_of_leave?.toLowerCase() === 'approved')
                .forEach(leave => {
                    const user = this.users?.find(u => u?.id === leave?.user_id) || null
                    let userName = 'Unknown User'
                    if (user?.profile?.first_name && user?.profile?.last_name) {
                        userName = `${user.profile.first_name.toUpperCase()} ${user.profile.last_name}`
                    } else if (user?.name) {
                        userName = user.name
                    }
                    const startDate = moment(leave.start_day, 'DD/MM/YYYY')
                    const endDate = moment(leave.end_day, 'DD/MM/YYYY')
                    let subject = userName
                    if (leave.type_of_leave === 'authorisation' && leave.start_time) {
                        const duration = parseFloat(leave.authorization_hour || 0);
                        if (duration > 0) {
                            const hoursFormatted = duration.toFixed(1) + 'h';
                            const endTimeDisplay = leave.end_time || moment(leave.start_time, 'HH:mm')
                                .add(duration, 'hours')
                                .format('HH:mm');
                            subject += ` | ${hoursFormatted} (${leave.start_time} - ${endTimeDisplay})`
                        }
                    } else if (leave.type_of_leave === 'halfday') {
                        subject += ` - ${leave.start_time === '08:00' ? 'Morning' : 'Afternoon'}`
                    }

                    const baseEvent = {
                        Id: leave.id,
                        Subject: subject,
                        Status: leave.status_of_leave,
                        Type: leave.type_of_leave,
                        FirstName: user?.profile?.first_name ?? '',
                        LastName: user?.profile?.last_name ?? ''
                    }

                    const segments = this.splitLeaveExcludeWeekends(startDate, endDate, baseEvent)
                    events.push(...segments)
                })

            // Ajouter les jours fériés de la base de données
            const currentYear = new Date().getFullYear();
            const holidayEvents = [];
            
            // Process admin-added holidays
            if (Array.isArray(this.holidays)) {
                this.holidays.forEach(holiday => {
                    const start = new Date(holiday.start_date);
                    const end = new Date(holiday.end_date);
                    let current = new Date(start);

                    while (current <= end) {
                        if (current.getFullYear() === currentYear || current.getFullYear() === currentYear + 1) {
                            const formattedDate = moment(current).format('DD/MM/YYYY');
                            holidayEvents.push({
                                Id: `holiday-${holiday.id}-${current.toISOString().split('T')[0]}`,
                                Subject: holiday.name,
                                StartTime: new Date(current),
                                EndTime: new Date(current),
                                IsAllDay: true,
                                CategoryColor: '#FF007F',
                                Type: 'holiday',
                                Status: 'official',
                                CssClass: 'holiday-event',
                                date: formattedDate,
                                IsReadonly: true,
                                Disabled: true
                            });
                        }
                        current.setDate(current.getDate() + 1);
                    }
                });
            }

            // Add static official holidays
            const officialHolidays = [
                { month: 0, day: 1, name: "Jour de l'An" },
                { month: 2, day: 20, name: "Fête de l'Indépendance" },
                { month: 3, day: 9, name: "Fête des Martyrs" }, 
                { month: 4, day: 1, name: "Fête du Travail" }, 
                { month: 6, day: 25, name: "Fête de la République" },
                { month: 7, day: 13, name: "Fête de la Femme" }, 
                { month: 9, day: 15, name: "Fête de l'Évacuation" }, 
                { month: 11, day: 17, name: "Fête de la Révolution" }
            ];

            officialHolidays.forEach(holiday => {
                const yearsToShow = [currentYear, currentYear + 1];
                yearsToShow.forEach(year => {
                    const holidayDate = new Date(year, holiday.month, holiday.day);
                    // Skip if this date is already covered by admin-added holidays
                    const isAlreadyAdded = holidayEvents.some(h => 
                        moment(h.StartTime).isSame(holidayDate, 'day')
                    );
                    
                    if (!isAlreadyAdded) {
                        const formattedDate = moment(holidayDate).format('DD/MM/YYYY');
                        holidayEvents.push({
                            Id: `official-holiday-${year}-${holiday.month}-${holiday.day}`,
                            Subject: holiday.name,
                            StartTime: new Date(holidayDate),
                            EndTime: new Date(holidayDate),
                            Type: 'holiday',
                            Status: 'official',
                            IsAllDay: true,
                            CssClass: 'holiday-event',
                            date: formattedDate,
                            CategoryColor: '#FF007F',
                            IsReadonly: true,
                            Disabled: true
                        });
                    }
                });
            });

            return [...events, ...holidayEvents];
        },
        mappedLeaves () {
            const leaves = Array.isArray(this.leaves) ? this.leaves : [];
            return leaves.map(leave => {
                const user = this.users?.find(user => user?.id === leave?.user_id) || {};
                const profile = user?.profile || {};
                const firstName = profile?.first_name || 'Unknown';
                const lastName = profile?.last_name || 'User';
                
                // Gestion des heures d'autorisation
                let endTime = leave?.end_time;
                let duration = 0;
                
                if (leave?.type_of_leave === 'authorisation' && leave?.start_time) {
                    // Try to get duration from authorization_hour first
                    if (leave.authorization_hour) {
                        duration = parseFloat(leave.authorization_hour);
                        const startTime = moment(leave.start_time, 'HH:mm');
                        const endMoment = startTime.clone().add(duration, 'hours');
                        endTime = endMoment.format('HH:mm');
                    }
                    // If we have both start and end time but no duration, calculate it
                    else if (leave.end_time && leave.start_time) {
                        const startTime = moment(leave.start_time, 'HH:mm');
                        const endTimeMoment = moment(leave.end_time, 'HH:mm');
                        duration = endTimeMoment.diff(startTime, 'hours', true);
                        endTime = leave.end_time;
                    }
                }
                
                // Get team name by checking which team includes this user in employee_ids
                let teamName = 'No Team';
                
                // Check if we have access to the teams data
                if (this.$page.props.teams) {
                    // Find the first team where this user is a member
                    const userTeam = this.$page.props.teams.find(team => 
                        team.employee_ids && team.employee_ids.includes(leave.user_id)
                    );
                    
                    if (userTeam) {
                        teamName = userTeam.team_name || 'No Team Name';
                    }
                }
                
                return {
                    id: leave?.id,
                    first_name: firstName,
                    last_name: lastName,
                    fullName: `${firstName} ${lastName}`,
                    user_id: leave?.user_id,
                    start_day: leave?.start_day,
                    start_time: leave?.start_time,
                    end_day: leave?.end_day,
                    end_time: endTime,
                    type_of_leave: leave?.type_of_leave,
                    status_of_leave: leave?.status_of_leave,
                    authorization_hours: parseFloat(duration || 0),
                    team_name: teamName
                };
            });
        },
        isAdmin () {
            return this.page.props.auth.user_roles.includes('admin')
        },
        isProjectManager () {
            return this.page.props.auth.user_roles.includes('project_manager')
        },
        // user leave hISTORY
        userLeaveHistory () {
            const currentUserId = this.page.props.auth.user.id
            const today = moment().startOf('day')
            return this.mappedLeaves.filter(leave => {
                const isUserLeave = leave.user_id === currentUserId
                if (!isUserLeave) {
                    return false
                }
                const leaveEndDateStr = leave.end_day || leave.start_day
                if (!leaveEndDateStr) {
                    return false
                }
                const leaveEndDate = moment(leaveEndDateStr, 'DD/MM/YYYY')
                return leaveEndDate.isBefore(today)
            })
        }
    },
    created () {
        this.localLeaves = [...this.leaves]
        this.users = this.$attrs.users
        this.mappedUsers = this.mapToOptions(this.users, ['profile.first_name', 'profile.last_name'], 'id')
    },
    methods: {
        // code pour les vacances
        getHolidayStyle(dateStr) {
            if (!dateStr) return {};
            // Formater la date pour la comparaison
            const [year, month, day] = dateStr.split('-').map(Number);
            const isHoliday = this.officialHolidays.some(holiday => holiday.month === month - 1 && holiday.day === day);
            if (isHoliday) {
                return {
                    color: '#d32f2f',
                    fontWeight: 'bold',
                    padding: '2px 5px',
                    borderRadius: '4px',
                    backgroundColor: 'rgba(255, 0, 0, 0.1)'
                };
            }
            return {};
        },
    // pour afficher les vacances
    onEventRender(args) {
    // Handle all holiday events (both from database and static)
    if (args.data.Type === 'holiday') {
        args.element.style.backgroundColor = '#FF007F';
        args.element.style.color = 'black';
        args.element.style.fontWeight = 'bold';
        args.element.style.border = '2px solid pink';
        args.element.title = args.data.Subject;
        args.element.style.width = '100%';
        args.element.style.height = '100%';
        
        // Disable interaction
        args.element.style.pointerEvents = 'none';
        args.element.style.opacity = '0.8';
        args.element.style.cursor = 'not-allowed';
        
        // Add emoji based on holiday type
        const emoji = args.data.Status === 'official' ? '🎉' : '🎊';
        
        args.element.innerHTML = `
            <div style="
                font-size:1em;
                color:black;
                margin-top:4px;
                display:flex;
                align-items:center;
                justify-content:center;
                gap:6px;
                font-weight:bold;
                pointer-events:none;
                opacity:0.9;">
                <span style="font-size:1.2em">${emoji}</span>
                <span>${args.data.Subject}</span>
            </div>`;
        return;
    }
    // ... rest of the event rendering logic ...
},
        customDateSort(event, field) {
            event.data.sort((a, b) => {
                const dateA = this.parseDate(a[field]);
                const dateB = this.parseDate(b[field]);
                if (dateA < dateB) return -1;
                if (dateA > dateB) return 1;
                return 0;
            });

            if (event.order === -1) {
                event.data.reverse();
            }
        },
        parseDate(dateStr) {
            if (!dateStr) return new Date(0);
            const parts = dateStr.split('/');
            if (parts.length !== 3) return new Date(0);
            const day = parts[0].padStart(2, '0');
            const month = parts[1].padStart(2, '0');
            const year = parts[2];

            return new Date(`${year}-${month}-${day}T00:00:00`);
        },
        approveLeave (leaveId) {
            router.post(route('leave.approve'), { id: leaveId }, {
                preserveScroll: true,
                onSuccess: this.refreshLeaves
            })
        },
        deleteLeave (leaveId) {
            router.post(route('leave.delete'), { id: leaveId }, {
                preserveScroll: true,
                onSuccess: () => {
                    this.localLeaves = this.localLeaves.filter(leave => leave.id !== leaveId)
                }
            })
        },
        refuseLeave () {
            this.leaveForm.post(route('leave.refuse'), {
                preserveScroll: true,
                onSuccess: this.refreshLeaves
            })
        },
        // code pour moi
 
    cancelLeave(leaveId) {
        router.post(route('leave.cancel'), { id: leaveId }, {
            preserveScroll: true,
                onSuccess: () => {
                    this.localLeaves = this.localLeaves.filter(leave => leave.id !== leaveId)
                }
            })
        },
        refreshLeaves () {
            router.visit(route('leaves.index'), {
                only: ['leaves'],
                preserveScroll: true,
                preserveState: true,
                onSuccess: (response) => {
                    this.localLeaves = response.props.leaves
                }
            })
        },
        mapToOptions (items, labelFields, valueField = 'id') {
            if (!Array.isArray(items)) return [];
            return items.map(item => ({
                value: item[valueField],
                label: Array.isArray(labelFields)
                    ? labelFields.map(field => this.getNestedValue(item, field)).join(' ')
                    : this.getNestedValue(item, labelFields)
            }))
        },
        onEventRender (args) {
            if (args.data.Status === 'approved') {
                if (args.data.Type === 'authorisation') {
                    args.element.style.backgroundColor = '#205fa9'
                } else if (args.data.Type === 'halfday') {
                    args.element.style.backgroundColor = '#8A2BE2'
                } else if (args.data.Type === 'sick') {
                    args.element.style.backgroundColor = '#203b48'
                } else if (args.data.Type === 'deduction') {
                    args.element.style.backgroundColor = '#FF0000'
                } else {
                    args.element.style.backgroundColor = 'green'
                }
            } else if (args.data.Status === 'pending') {
                args.element.style.backgroundColor = 'orange'
            }
            if (args.data.Type === 'holiday') {
    args.element.style.backgroundColor='#FF007F'
    args.element.title = args.data.Subject;
  }

        },
        getNestedValue (item, field) {
            return field.split('.').reduce((obj, key) => obj && obj[key], item)
        },
        openDialog () {
            // Effacer les messages d'erreur précédents
            if (this.$page.props.flash) {
                this.$page.props.flash.error = null;
            }
            this.showDialog = true;
        },
        openHistoryDialog () { 
            this.showHistoryDialog = true
        },
        openRefuseDialog (id) {
            this.refusedLeave = id
            this.showRefuseDialog = true
        },
        closeDialog() {
            this.showDialog = false;
            this.leaveForm.reset();
            // Effacer le message d'erreur
            if (this.$page.props.flash) {
                this.$page.props.flash.error = null;
            }
        },
        getStartTime () {
            switch (this.leaveForm.type_of_leave) {
                case 'halfday':
                    return this.leaveForm.halfday_session === 'morning' ? '08:00' : '12:00'
                default:
                    return this.leaveForm.start_time
            }
        },
 splitLeaveExcludeWeekends(startDate, endDate, baseEvent) {
    const events = [];
    let currentDate = new Date(startDate);
    const end = new Date(endDate);
    while (currentDate <= end) {
        // Ne pas ajouter d'événement pour les week-ends et jours fériés
        if (!this.isWeekendOrHoliday(currentDate)) {
            const eventDate = new Date(currentDate);
            const formattedDate = moment(eventDate).format('DD/MM/YYYY');
            events.push({
                ...baseEvent,
                StartTime: eventDate,
                EndTime: eventDate,
                date: formattedDate
            });
        }
        
        //passer au jour suivant
        currentDate.setDate(currentDate.getDate() + 1);
    }
    
    return events;
},

        isWeekend(date) {
            const dayOfWeek = date.getDay();
            return dayOfWeek === 0 || dayOfWeek === 6;
        },

        isHoliday(date) {
            if (!this.holidays || !this.holidays.length) return false;

            return this.holidays.some(holiday => {
                const startDate = new Date(holiday.start_date);
                const endDate = new Date(holiday.end_date);
                const checkDate = new Date(date.getFullYear(), date.getMonth(), date.getDate()); // Remove time part

                return checkDate >= startDate && checkDate <= endDate;
            });
        },

        isWeekendOrHoliday(date) {
            return this.isWeekend(date) || this.isHoliday(date);
        },
        openAddHolidayDialog() {
            this.isAddHolidayDialogOpen = true;
        },
        closeModal() {
            this.isAddHolidayDialogOpen = false;
            this.holidayForm.reset();
        },
        submitHoliday() {
            this.holidayForm.post(route('holidays.store'), {
                onSuccess: () => this.closeModal(),
            });
        },
        submitLeaveRequest() {
            this.processingLeaveRequest = true;
            
            if (!this.leaveForm.user_id) {
                this.leaveForm.user_id = this.$page.props.auth.user.id;
            }
            
            if (!this.leaveForm.end_day) {
                this.leaveForm.end_day = this.leaveForm.start_day;
            }
            
            if (this.leaveForm.type_of_leave === 'halfday') {
                this.leaveForm.start_time = this.leaveForm.session === 'morning' ? '08:00' : '13:00';
                this.leaveForm.end_time = this.leaveForm.session === 'morning' ? '12:00' : '17:00';
            }
            
            if (this.leaveForm.type_of_leave === 'authorisation' && !this.leaveForm.authorisation_hour) {
                this.toast.error('Veuillez sélectionner le nombre d\'heures d\'autorisation');
                this.processingLeaveRequest = false;
                this.isButtonDisabled = true;
                return;
            }
            
            this.leaveForm.post(route('leaves.store'), {
                preserveScroll: true,
                onSuccess: () => {
                    this.processingLeaveRequest = false
                    this.isButtonDisabled = false;
                },
                onError: (errors) => {
            if (errors.message) {
                this.overlapWarning = errors.message;
                this.$page.props.flash = { error: errors.message };
                this.hasOverlap = true;
                this.isButtonDisabled = true;
            }
            this.processingLeaveRequest = false;
        },
                onFinish: () => {
                    this.processingLeaveRequest = false;
                    this.isButtonDisabled = false;
                }
            });
        },
    
        // Méthode appelée lors du clic sur une cellule du calendrier
        onCellClick(args) {
            // Vérifier si la date cliquée est un jour férié
            const clickedDate = args.startTime;
            const isHoliday = this.holidays.some(holiday => {
                const start = new Date(holiday.start_date);
                const end = new Date(holiday.end_date);
                return clickedDate >= start && clickedDate <= end;
            });

            // Si c'est un jour férié, annuler l'action par défaut
            if (isHoliday) {
                args.cancel = true;
                return;
            }
        },
        resetPagination() {
            if (this.$refs.dt) {
                this.$refs.dt.first = 0; // Reset to first page
            }
        },
    },
    watch: {
        approvedLeaves: {
            handler (newLeaves) {
                this.eventSettings = { ...this.eventSettings, dataSource: newLeaves }
            },
            deep: true,
            immediate: true
        }
    },
}
</script>

<template>
    <Head title="Leave Request"/>
    <AuthenticatedLayout>
        <div class="py-12">
            <div class="mx-auto space-y-6 sm:px-6 lg:px-8">
                <div class="bg-white p-4 shadow sm:rounded-lg sm:p-8">
                    <div class="flex justify-between py-5 gap-2">
                        <div class="flex gap-5 overflow-x-auto">
                            <div class="manuel-item flex gap-2 items-center"><span
                                class="is-square is-green-square"></span> Vacation Leave
                            </div>
                            <div class="manuel-item flex gap-2 items-center"><span
                                class="is-square is-darkBlue-square"></span> Sick Leave
                            </div>
                            <div class="manuel-item flex gap-2 items-center"><span
                                class="is-square is-violet-square"></span> Halfday
                            </div>
                            <div class="manuel-item flex gap-2 items-center"><span
                                class="is-square is-blue-square"></span> Authorisation
                            </div>
                            <div class="manuel-item flex gap-2 items-center"><span
                                class="is-square is-orange-square"></span> Pending Request
                            </div>
                            <div class="manuel-item flex gap-2 items-center"><span
                                class="is-square is-red-square"></span> Deduction
                            </div>
                            <div class="manuel-item flex gap-2 items-center"><span
                                class="is-square is-pink-square"></span> Holidays
                            </div>
                        </div>
                        <div class="flex flex-col items-end gap-2">
                            <div v-if="$page.props.flash?.error" class="bg-red-100 border-l-4 border-red-500 text-red-700 p-2 rounded">
                                <p class="font-medium">{{ $page.props.flash.error }}</p>
                            </div>
                            <div class="flex gap-2">
                                <PrimaryButton @click="openHistoryDialog" class="bg-yellow-400 text-white">
                                    Leave History
                                </PrimaryButton>
                                <PrimaryButton v-if="isAdmin" @click="openAddHolidayDialog" class="bg-orange-600 text-white">
                                    Add Holiday
                                </PrimaryButton>
                                <PrimaryButton @click="openDialog" class="bg-green-600 text-white">
                                    Add Leave Request
                                </PrimaryButton>
                            </div>
                        </div>
                    </div>
                    <ejs-schedule
                        :event-settings="eventSettings"
                        :views="views"
                        :selected-date="selectedDate"
                        height="750px"
                        :event-rendered="onEventRender"
                        :first-day-of-week="1"
                        :cell-click="onCellClick"
                        ref="schedule"
                    ></ejs-schedule>
                </div>
            </div>
            <div class="mx-auto space-y-6 sm:px-6 lg:px-8 py-5">
                <div class="bg-white p-4 shadow sm:rounded-lg sm:p-8">
                    <div class="mt-6">
                        <h3 class="text-lg font-bold mb-4">Leave Requests </h3>
                        <DataTable
                            ref="dt"
                            :value="mappedLeaves"
                            dataKey="id"
                            :paginator="true"
                            :rows="10"
                            v-model:selection="selectedLeaves"
                            :filters="filters"
                            paginatorTemplate="FirstPageLink PrevPageLink PageLinks NextPageLink LastPageLink CurrentPageReport RowsPerPageDropdown"
                            :rowsPerPageOptions="[5, 10, 25]"
                            currentPageReportTemplate="Showing {first} to {last} of {totalRecords} leaves"
                            :globalFilterFields="['first_name', 'last_name', 'fullName', 'type_of_leave']"
                        >
                            <template #header>
                                <div class="flex justify-content-end">
                                    <span class="p-input-icon-left">
                                        <i class="pi pi-search" />
                                        <InputText 
                                            v-model="filters['global'].value" 
                                            placeholder="Search by last name or leave type"
                                            @input="resetPagination"
                                        />
                                    </span>
                                </div>
                            </template>

                            <template #empty>
                                <h4>No leaves found</h4>
                            </template>

                            <Column field="first_name" header="First Name" :sortable="true" />
                            <Column field="last_name" header="Last Name" :sortable="true" />
                            
                            <Column field="start_day" header="Start Day" :sortable="true"
                                :sort-field="(row) => parseDate(row.start_day).getTime()"
                                :sort-function="(event) => customDateSort(event, 'start_day')">
                                <template #body="slotProps">
                                    <span :style="getHolidayStyle(slotProps.data.start_day)">
                                        {{ slotProps.data.start_day }}
                                    </span>
                                </template>
                            </Column>
                            
                            <Column field="end_day" header="End Day" :sortable="true"
                                :sort-field="(row) => parseDate(row.end_day).getTime()"
                                :sort-function="(event) => customDateSort(event, 'end_day')">
                                <template #body="slotProps">
                                    <span :style="getHolidayStyle(slotProps.data.end_day)">
                                        {{ slotProps.data.end_day }}
                                    </span>
                                </template>
                            </Column>

                            <Column field="type_of_leave" header="Type of Leave" :sortable="true"/>
                            
                            <Column v-if="isProjectManager" field="team_name" header="Team" :sortable="true">
                                <template #body="{ data }">
                                    {{ data.team_name }}
                                </template>
                            </Column>
                            
                            <Column field="authorization_hours" header="Authorisation Hours" :sortable="true">
                                <template #body="slotProps">
                                    <span v-if="slotProps.data.type_of_leave === 'authorisation'" 
                                        :class="[
                                            'px-3 py-1 rounded-full',
                                            slotProps.data.authorization_hours > 0 ? 'bg-blue-100' : 'bg-gray-100'
                                        ]">
                                        {{ parseFloat(slotProps.data.authorization_hours || 0).toFixed(1) }}h
                                        <span class="text-gray-600 ml-1" v-if="slotProps.data.start_time && slotProps.data.end_time">
                                            ({{ slotProps.data.start_time }} - {{ slotProps.data.end_time }})
                                        </span>
                                    </span>
                                </template>
                            </Column>
                            
                            <Column bodyClass="text-center" field="status_of_leave" header="Status of Leave" :sortable="true">
                                <template #body="slotProps">
                                    <Tag v-if="slotProps.data.status_of_leave === 'approved'" severity="success" value="Approved"/>
                                    <Tag v-else-if="slotProps.data.status_of_leave === 'pending'" severity="warn" value="Pending"/>
                                    <Tag v-else severity="danger" value="Refused"/>
                                </template>
                            </Column>

                            <Column field="action" header="Action" bodyClass="text-center" :sortable="true">
                                <template #body="slotProps">
                                    <div class="flex flex-wrap gap-2 justify-center">
                                        <!-- Pending Actions -->
                                        <template v-if="slotProps.data.status_of_leave === 'pending'">
                                            <!-- Admin/PM can approve or reject -->
                                            <template v-if="isAdmin || isProjectManager">
                                                <PrimaryButton 
                                                    @click="approveLeave(slotProps.data.id)"
                                                    class="bg-blue-600 text-white"
                                                >
                                                    Approve
                                                </PrimaryButton>
                                                <PrimaryButton 
                                                    @click="openRefuseDialog(slotProps.data.id)"
                                                    class="bg-red-600 text-white"
                                                >
                                                    Reject
                                                </PrimaryButton>
                                            </template>
                                       
                                            <!-- User can cancel their own pending request -->
                                            <template v-else-if="slotProps.data.user_id === page.props.auth.user.id">
                                                <PrimaryButton
                                                    @click="cancelLeave(slotProps.data.id)"
                                                    class="bg-pink-600 text-white"
                                                >
                                                    <svg 
                                                        class="w-5 h-5 mr-1 inline" 
                                                        fill="none" 
                                                        stroke="currentColor" 
                                                        stroke-width="2" 
                                                        viewBox="0 0 24 24"
                                                    >
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                                                    </svg>
                                                    Cancel
                                                </PrimaryButton>
                                            </template>
                                        </template>

                                        <!-- Remove button always visible for admin/pm -->
                                        <template v-if="isAdmin || isProjectManager">
                                            <PrimaryButton 
                                                @click="deleteLeave(slotProps.data.id)"
                                                class="bg-gray-600 text-white"
                                            >
                                                Remove
                                            </PrimaryButton>
                                        </template>
                                    </div>
                                </template>
                            </Column>
                        </DataTable>
                        </div>
                    </div>
                </div>
            </div>
      
        
        <Dialog
            v-model:visible="showDialog"
            :closable="true"
            :modal="true"
            header="Leave Request"
            @hide="closeDialog"
            class="rounded-lg shadow-lg p-5 bg-white w-[95%] sm:w-[80%] md:w-[60%] lg:w-[50%] max-w-3xl mx-auto"
        >
            <!-- Message d'erreur -->
            <div v-if="$page.props.flash?.error" class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-4 rounded">
                <div class="flex items-center">
                    <svg class="h-5 w-5 text-red-500 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span>{{ $page.props.flash.error }}</span>
                </div>
            </div>

            <div class="space-y-4">
                <section>
                    <h3 class="text-lg font-medium mb-2">Leave Type</h3>
                    <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-3">
                        <button
                            v-for="(type, index) in filteredLeaveTypes"
                            :key="index"
                            @click="leaveForm.type_of_leave = type.value"
                            class="w-full text-center py-2 px-3 rounded-lg border hover:bg-gray-100"
                            :class="{'bg-blue-500 text-white': leaveForm.type_of_leave === type.value}"
                        >
                            {{ type.label }}
                        </button>
                    </div>
                </section>

                <section v-if="isAdmin ">
                    <label class="font-medium">Select User:</label>
                    <Select
                        v-model="leaveForm.team_user"
                        :options="mappedUsers"
                        :filter="true"
                        optionLabel="label"
                        optionValue="value"
                        placeholder="Choose a user"
                        class="w-full border rounded-lg p-2"
                    />
                </section>

                <section v-if="leaveForm.type_of_leave !== 'deduction'">
                    <label class="font-medium">
                        {{
                            leaveForm.type_of_leave === 'authorisation' || leaveForm.type_of_leave === 'halfday'
                                ? 'Date'
                                : 'Start Date'
                        }}
                    </label>
                    <DatePicker
                        v-model="leaveForm.start_day"
                        dateFormat="dd-mm-yy"
                        :disabled-days="[0,6]"
                        :disabled-dates="disabledDates" 
                        class="w-full rounded-lg p-2"
                        placeholder="Select date"
                        :minDate="new Date()"
                        showIcon
                        @date-select="checkOverlap"
                    />

                    <div v-if="leaveForm.type_of_leave !== 'authorisation' && leaveForm.type_of_leave !== 'halfday'">
                        <label class="font-medium">End Date:</label>
                        <DatePicker
                            v-model="leaveForm.end_day"
                            dateFormat="dd-mm-yy"
                            :disabled-days="[0,6]"
                            :disabled-dates="disabledDates" 
                            class="w-full rounded-lg p-2"
                            :disabled="leaveForm.type_of_leave === 'halfday' || leaveForm.type_of_leave === 'authorisation'"
                            placeholder="Select end date"
                            :minDate="leaveForm.start_day || new Date()"
                            showIcon
                            @date-select="checkOverlap"
                        />
                    </div>
                </section>

                <section v-if="leaveForm.type_of_leave === 'halfday'">
                    <label class="font-medium">Session:</label>
                    <Select
                        v-model="leaveForm.session"
                        :options="sessionOptions"
                        optionLabel="label"
                        optionValue="value"
                        placeholder="Choose session"
                        class="w-full border rounded-lg p-2"
                        required
                    />
                    <InputError class="mt-2" :message="leaveForm.errors.session" />
                </section>

                <template v-if="leaveForm.type_of_leave === 'authorisation'">
                    <section>
                        <label class="font-medium">Time:</label>
                        <Select
                            v-model="leaveForm.start_time"
                            :options="timeOptions"
                            optionLabel="label"
                            optionValue="value"
                            placeholder="Choose time"
                            class="w-full border rounded-lg p-2"
                        />
                    </section>

                    <section>
                        <label class="font-medium">Authorisation Hours (0.5 - 2h per Month):</label>
                        <div class="grid grid-cols-4 gap-2">
                            <button
                            
                                v-for="hour in [0.5, 1, 1.5, 2]"
                                :key="hour"
                                @click="leaveForm.authorisation_hour = hour"
                                class="w-full text-center py-2 rounded-lg border hover:bg-gray-100"
                                :class="{'bg-blue-500 text-white': leaveForm.authorisation_hour === hour}"
                            >
                                {{ hour }}h
                            </button>
                        </div>
                    </section>
                </template>

                <template v-if="isAdmin && leaveForm.type_of_leave === 'deduction'">
                    <section>
                        <label class="font-medium">Deducted Leave Days:</label>
                        <InputNumber
                            v-model="leaveForm.deduction_days"
                            :minFractionDigits="2" :maxFractionDigits="5"
                            fluid
                            class="w-full border rounded-lg p-2"
                            placeholder="Enter days to deduct"
                        />
                    </section>
                </template>

                <div class="flex justify-end gap-3 mt-6">
                    <button
                        type="button"
                        @click="closeDialog"
                        class="px-4 py-2 bg-gray-300 text-gray-700 rounded-lg hover:bg-gray-400 transition duration-200"
                    >
                        Annuler
                    </button>
                    <button
                        type="button"
                        @click="submitLeaveRequest"
                        :disabled="processingLeaveRequest || isCheckingOverlap || hasOverlap || !leaveForm.type_of_leave || !leaveForm.start_day || (leaveForm.type_of_leave === 'halfday' && !leaveForm.session) || (leaveForm.type_of_leave === 'authorisation' && !leaveForm.start_time)"
                        :class="{
                            'opacity-50 cursor-not-allowed': hasOverlap || isCheckingOverlap,
                            'bg-blue-500 hover:bg-blue-600 text-white': !hasOverlap && !isCheckingOverlap,
                            'bg-gray-300': hasOverlap || isCheckingOverlap
                        }"
                        class="px-4 py-2 rounded-lg transition duration-200"
                    >
                        <span v-if="processingLeaveRequest || isCheckingOverlap" class="flex items-center">
                            <i class="pi pi-spin pi-spinner mr-2"></i> {{ isCheckingOverlap ? 'Vérification...' : 'Traitement...' }}
                        </span>
                        <span v-else-if="hasOverlap">Demande non disponible</span>
                        <span v-else>Demander un congé</span>
                    </button>
                </div>

            </div>
        </Dialog>
        <Dialog
            v-model:visible="showRefuseDialog"
            header="Reason for Refusal"
            :showCloseIcon="true"
            width="420px"
            @close="closeDialog"
        >
            <div class="p-4 py-5">
                <div class="mb-4">
                    <label for="refuseReason" class="block text-sm font-medium text-gray-700 mb-2">
                        Please provide the reason for refusal
                    </label>
                    <textarea
                        id="refuseReason"
                        v-model="refuseReason"
                        rows="4"
                        class="w-full border border-gray-300 p-2 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                        placeholder="Enter reason here..."
                    ></textarea>
                </div>

                <div class="flex justify-end gap-2 mt-4">
                    <PrimaryButton @click="refuseLeave()" class="bg-blue-500 text-white">
                        Refuse Request
                    </PrimaryButton>
                    <PrimaryButton @click="closeDialog" class="bg-red-600 text-white">
                        Cancel
                    </PrimaryButton>
                </div>
            </div>
        </Dialog>
        <Dialog
            v-model:visible="isAddHolidayDialogOpen"
            :closable="true"
            :modal="true"
            header="Add New Holiday"
            class="rounded-lg shadow-lg p-5 bg-white w-[95%] sm:w-[80%] md:w-[60%] lg:w-[50%] max-w-3xl mx-auto"
        >
                <form @submit.prevent="submitHoliday" class="mt-6">
                    <div>
                        <InputLabel for="name" value="Holiday Name" />

                        <TextInput
                            id="name"
                            type="text"
                            class="mt-1 block w-full"
                            v-model="holidayForm.name"
                            required
                            autofocus
                        />

                        <InputError class="mt-2" :message="holidayForm.errors.name" />
                    </div>
                    <div class="mt-4">
                        <InputLabel for="Start Day" value="Start Day" />

                        <TextInput
                            id="Start Day"
                            type="date"
                            class="mt-1 block w-full"
                            v-model="holidayForm.start_date"
                            required
                        />
                       
                        <InputError class="mt-2" :message="holidayForm.errors.start_date" />
                    </div>
                    <div class="mt-4">
                        <InputLabel for="End Day" value="End Day" />

                        <TextInput
                            id="End Day"
                            type="date"
                            class="mt-1 block w-full"
                            v-model="holidayForm.end_date"
                            required
                        />

                        <InputError class="mt-2" :message="holidayForm.errors.end_date" />
                    </div>
                    <div class="mt-6 flex justify-end">
                        <SecondaryButton @click="closeModal"> Cancel </SecondaryButton>

                        <PrimaryButton
                            class="ml-3"
                            :class="{ 'opacity-25': holidayForm.processing }"
                            :disabled="holidayForm.processing"
                        >
                            Save Holiday
                        </PrimaryButton>
                    </div>
                </form>
          
        </Dialog>
        <loading v-model:active="processingLeaveRequest"
                 :can-cancel="false"
                 :is-full-page="true"/>
    </AuthenticatedLayout>
</template>
