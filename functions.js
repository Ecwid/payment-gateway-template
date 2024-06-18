// Initialize the application

EcwidApp.init({
    app_id: "test-rick-payment-template", // use your application namespace
    autoloadedflag: true,
    autoheight: true
});

var storeData = EcwidApp.getPayload();

var storeId = storeData.store_id;
var accessToken = storeData.access_token;
var language = storeData.lang;
var viewMode = storeData.view_mode;

if (storeData.public_token !== undefined) {
    var publicToken = storeData.public_token;
}

if (storeData.app_state !== undefined) {
    var appState = storeData.app_state;
}

// Function to go to edit product label page

function showEditPage(elementClass) {
    document.querySelector('.main').style.display = 'none';
    document.querySelector(elementClass).style.display = 'block';
    document.querySelector('.content-control-menu-nav').style.display = 'flex';
}


// Function to retutn back to main app page after editing product label page

function goBack() {
    document.querySelector('.content-control-menu-nav').style.display = 'none';
    document.querySelector('.main').style.display = 'block';

    // hide all separate pages for editing
    for (i = 0; i < document.querySelectorAll('.separate-editing-page').length; i++) {
        document.querySelectorAll('.separate-editing-page')[i].style.display = 'none';
    }
}


// Reads values from HTML page and sends them to application config
// To fill values successfully, the input, select or textarea elements on a page must have 'data-name' and 'data-visibility' attributes set. See appProto.html for examples

function readValuesFromPage() {
    var applicationConfig = {
        public: {},
        private: {}
    }

    var allInputs = document.querySelectorAll('input, select, textarea');

    for (i = 0; i < allInputs.length; i++) {
        var fieldVisibility = allInputs[i].dataset.visibility;

        if (fieldVisibility !== undefined) {
            if (allInputs[i].tagName == "INPUT") {

                if (allInputs[i].type == 'checkbox' || allInputs[i].type == 'radio') {
                    applicationConfig[fieldVisibility][allInputs[i].dataset.name] = String(allInputs[i].checked);
                }
                if (allInputs[i].type == 'text' || allInputs[i].type == 'number' || allInputs[i].type == 'date') {
                    applicationConfig[fieldVisibility][allInputs[i].dataset.name] = allInputs[i].value;
                }
            }
            if (allInputs[i].tagName == "SELECT" || allInputs[i].tagName == "TEXTAREA") {
                applicationConfig[fieldVisibility][allInputs[i].dataset.name] = allInputs[i].value;
            }
        }
    }

    applicationConfig.public = JSON.stringify(applicationConfig.public);

    return applicationConfig;
}

// Reads values from provided config and sets them for inputs on the page.
// To fill values successfully, the input, select or textarea elements must have 'data-name' and 'data-visibility' attributes set. See appProto.html for examples

function setValuesForPage(applicationConfig) {
    var applicationConfigTemp = {
        public: {},
        private: {}
    };

    // for cases when we get existing users' data

    if (applicationConfig.constructor === Array) {
        for (i = 0; i < applicationConfig.length; i++) {
            if (applicationConfig[i].key !== 'public') {
                applicationConfigTemp.private[applicationConfig[i].key] = applicationConfig[i].value;
            } else {
                applicationConfigTemp[applicationConfig[i].key] = applicationConfig[i].value;
            }
        }
        applicationConfig = applicationConfigTemp;
    }

    applicationConfig.public = JSON.parse(applicationConfig.public);
    var allInputs = document.querySelectorAll('input, select, textarea');

    // Set values from config for input, select, textarea elements

    for (i = 0; i < allInputs.length; i++) {
        var fieldVisibility = allInputs[i].dataset.visibility;

        if (fieldVisibility !== undefined && applicationConfig[fieldVisibility][allInputs[i].dataset.name] !== undefined) {
            if (allInputs[i].tagName == "INPUT") {

                if (allInputs[i].type == 'checkbox' || allInputs[i].type == 'radio') {
                    allInputs[i].checked = (applicationConfig[fieldVisibility][allInputs[i].dataset.name] == "true");
                    checkFieldChange(allInputs[i]);
                }
                if (allInputs[i].type == 'text' || allInputs[i].type == 'number' || allInputs[i].type == 'date') {
                    allInputs[i].value = applicationConfig[fieldVisibility][allInputs[i].dataset.name];
                    checkFieldChange(allInputs[i]);
                }
            }
            if (allInputs[i].tagName == "SELECT" || allInputs[i].tagName == "TEXTAREA") {
                allInputs[i].value = applicationConfig[fieldVisibility][allInputs[i].dataset.name];
                checkFieldChange(allInputs[i]);
            }
        }
    }
}

// Default settings for new accounts

var initialConfig = {
    private: {
        merchantId: "",
        APIsecret: "",
        endpointUrl: "https://secure2.authorize.net/gateway/transact.dll",
        testMode: "true",
        installed: "yes"
    },
    public: {}
};

initialConfig.public = JSON.stringify(initialConfig.public);

// Executes when we have a new user install the app. It creates and sets the default data using Ecwid JS SDK and Application storage
function createUserData() {
    // Saves data for application storage
    EcwidApp.setAppStorage(initialConfig.private, function (value) {
        console.log('Initial private user preferences saved!');
    });

    // Saves data for public app config
    EcwidApp.setAppPublicConfig(initialConfig.public, function (value) {
        console.log('Initial public user preferences saved!');
    });

    // Function to prepopulate values of select, input and textarea elements based on default settings for new accounts
    setValuesForPage(initialConfig);
}


// Executes if we have a user who logs in to the app not the first time. We load their preferences from Application storage with Ecwid JS SDK and display them in the app interface

function getUserData() {
    // Retrieve all keys and values from application storage, including public app config. Set the values for select, input and textarea elements on a page in a callback
    EcwidApp.getAppStorage(function (allValues) {
        setValuesForPage(allValues);
    });
}

// Executes when we need to save data. Gets all elements' values and saves them to Application storage and public app config via Ecwid JS SDK

function saveUserData() {
    var saveData = readValuesFromPage();

    EcwidApp.setAppStorage(saveData.private, function (savedData) {
        console.log('Private preferences saved!');
    });

    EcwidApp.setAppPublicConfig(saveData.public, function (savedData) {
        console.log('Public preferences saved!');
    });
}

function resetUserData(initialConfig) {
    setValuesForPage(initialConfig);
    saveUserData();
}


// Main app function to determine if the user is new or just logs into the app

EcwidApp.getAppStorage('installed', function (value) {
    if (value !== null) {
        getUserData();
    } else {
        createUserData();
    }
})


