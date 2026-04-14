package edu.connexion3a36.Controller;

import edu.connexion3a36.entities.Quiz;
import edu.connexion3a36.services.QuizService;
import javafx.collections.FXCollections;
import javafx.collections.ObservableList;
import javafx.event.ActionEvent;
import javafx.fxml.FXML;
import javafx.fxml.FXMLLoader;
import javafx.scene.Node;
import javafx.scene.Parent;
import javafx.scene.Scene;
import javafx.scene.control.*;
import javafx.scene.control.cell.PropertyValueFactory;
import javafx.stage.Stage;

import java.io.IOException;
import java.sql.SQLException;

public class ListeQuizController {

    @FXML
    private TableView<Quiz> quizzesTable;

    @FXML
    private TableColumn<Quiz, Integer> colId;

    @FXML
    private TableColumn<Quiz, String> colTitle;

    @FXML
    private TableColumn<Quiz, String> colStatus;

    @FXML
    private Label selectionLabel;

    private final QuizService quizService = new QuizService();
    private ObservableList<Quiz> masterData = FXCollections.observableArrayList();

    @FXML
    private javafx.scene.control.TextField searchTF;

    @FXML
    public void initialize() {
        colId.setCellValueFactory(new PropertyValueFactory<>("idQuiz"));
        colTitle.setCellValueFactory(new PropertyValueFactory<>("titre"));
        colStatus.setCellValueFactory(new PropertyValueFactory<>("statut"));
        chargerQuiz();
    }

    public void chargerQuiz() {
        try {
            masterData = FXCollections.observableArrayList(quizService.afficherQuiz());
            quizzesTable.setItems(masterData);
        } catch (SQLException e) {
            showAlert("Erreur", "Erreur lors du chargement des quiz : " + e.getMessage());
        }
    }

    @FXML
    private void handleSearch(javafx.event.ActionEvent event) {
        String q = searchTF.getText() == null ? "" : searchTF.getText().trim().toLowerCase();
        if (q.isEmpty()) {
            quizzesTable.setItems(masterData);
            return;
        }
        ObservableList<Quiz> filtered = FXCollections.observableArrayList();
        for (Quiz quiz : masterData) {
            if (String.valueOf(quiz.getIdQuiz()).contains(q) || (quiz.getTitre() != null && quiz.getTitre().toLowerCase().contains(q)) || (quiz.getStatut() != null && quiz.getStatut().toLowerCase().contains(q))) {
                filtered.add(quiz);
            }
        }
        quizzesTable.setItems(filtered);
    }

    public Quiz getSelectedQuiz() {
        return quizzesTable.getSelectionModel().getSelectedItem();
    }

    @FXML
    private void handleAdd(ActionEvent event) {
        openFXML(event, "/quiz_add.fxml");
    }

    @FXML
    private void handleEdit(ActionEvent event) {
        if (getSelectedQuiz() == null) {
            showAlert("Info", "Sélectionnez un quiz à modifier.");
            return;
        }
        openFXML(event, "/quiz_edit.fxml");
    }

    @FXML
    private void handleDelete(ActionEvent event) {
        if (getSelectedQuiz() == null) {
            showAlert("Info", "Sélectionnez un quiz à supprimer.");
            return;
        }
        Alert confirm = new Alert(Alert.AlertType.CONFIRMATION, "Supprimer le quiz sélectionné ?", ButtonType.YES, ButtonType.NO);
        confirm.showAndWait();
        if (confirm.getResult() == ButtonType.YES) {
            try {
                quizService.supprimerQuiz(getSelectedQuiz().getIdQuiz());
                ControllerUtils.showInfo("Quiz supprimé.");
                chargerQuiz();
            } catch (SQLException e) {
                ControllerUtils.showError("Erreur lors de la suppression : " + e.getMessage());
            }
        }
    }

    @FXML
    private void handleValidate(ActionEvent event) {
        if (getSelectedQuiz() == null) { showAlert("Info", "Sélectionnez un quiz à valider."); return; }
        try {
            quizService.validerQuiz(getSelectedQuiz().getIdQuiz());
            ControllerUtils.showInfo("Quiz validé.");
            chargerQuiz();
        } catch (SQLException e) {
            ControllerUtils.showError("Erreur lors de la validation : " + e.getMessage());
        }
    }

    @FXML
    private void handleReject(ActionEvent event) {
        if (getSelectedQuiz() == null) { showAlert("Info", "Sélectionnez un quiz à rejeter."); return; }
        try {
            quizService.rejeterQuiz(getSelectedQuiz().getIdQuiz());
            ControllerUtils.showInfo("Quiz rejeté.");
            chargerQuiz();
        } catch (SQLException e) {
            ControllerUtils.showError("Erreur lors du rejet : " + e.getMessage());
        }
    }

    @FXML
    private void handleRefresh(ActionEvent event) {
        chargerQuiz();
    }

    @FXML
    private void handleBack(ActionEvent event) {
        try {
            FXMLLoader loader = new FXMLLoader(getClass().getResource("/home.fxml"));
            Parent root = loader.load();
            Node source = (Node) event.getSource();
            Stage stage = (Stage) source.getScene().getWindow();
            Scene scene = stage.getScene();
            if (scene == null) {
                Scene newScene = new Scene(root, 1100, 700);
                newScene.getStylesheets().add(getClass().getResource("/style.css").toExternalForm());
                stage.setScene(newScene);
            } else {
                scene.setRoot(root);
            }
        } catch (IOException e) {
            showAlert("Erreur", "Impossible de retourner à l'accueil: " + e.getMessage());
        }
    }

    private void openFXML(ActionEvent event, String fxmlPath) {
        try {
            FXMLLoader loader = new FXMLLoader(getClass().getResource(fxmlPath));
            Parent root = loader.load();
            Object controller = loader.getController();
            // If the controller has a setQuiz method, pass the selected quiz
            try {
                if (controller != null) {
                    java.lang.reflect.Method m = null;
                    try { m = controller.getClass().getMethod("setQuiz", Quiz.class); } catch (NoSuchMethodException ignored) {}
                    if (m != null) m.invoke(controller, getSelectedQuiz());
                }
            } catch (Exception ignored) {}
            Stage stage = (Stage) ((Node) event.getSource()).getScene().getWindow();
            Scene scene = stage.getScene();
            if (scene == null) {
                Scene newScene = new Scene(root, 1100, 700);
                newScene.getStylesheets().add(getClass().getResource("/style.css").toExternalForm());
                stage.setScene(newScene);
            } else {
                scene.setRoot(root);
            }
        } catch (IOException e) {
            showAlert("Erreur", "Impossible d'ouvrir la page: " + e.getMessage());
        }
    }

    private void showAlert(String title, String content) {
        Alert a = new Alert(Alert.AlertType.INFORMATION);
        a.setTitle(title);
        a.setHeaderText(null);
        a.setContentText(content);
        a.showAndWait();
    }
}
