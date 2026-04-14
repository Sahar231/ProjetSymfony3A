package edu.connexion3a36.Controller;

import edu.connexion3a36.entities.Question;
import edu.connexion3a36.services.QuestionService;
import javafx.collections.FXCollections;
import javafx.collections.ObservableList;
import javafx.event.ActionEvent;
import javafx.fxml.FXML;
import javafx.fxml.FXMLLoader;
import javafx.scene.Node;
import javafx.scene.Parent;
import javafx.scene.Scene;
import javafx.scene.control.Alert;
import javafx.scene.control.Label;
import javafx.scene.control.TableColumn;
import javafx.scene.control.TableView;
import javafx.scene.control.cell.PropertyValueFactory;
import javafx.stage.Stage;

import java.io.IOException;
import java.sql.SQLException;

public class ListeQuestionController {

    @FXML
    private TableView<Question> questionsTable;

    @FXML
    private TableColumn<Question, Integer> colId;

    @FXML
    private TableColumn<Question, String> colQuestion;

    @FXML
    private TableColumn<Question, String> colCorrectAnswer;

    @FXML
    private TableColumn<Question, Double> colScore;

    @FXML
    private TableColumn<Question, String> colType;

    private final QuestionService questionService = new QuestionService();
    private ObservableList<Question> masterData = FXCollections.observableArrayList();

    @FXML
    private javafx.scene.control.TextField searchTF;

    @FXML
    public void initialize() {
        colId.setCellValueFactory(new PropertyValueFactory<>("idQuestion"));
        colQuestion.setCellValueFactory(new PropertyValueFactory<>("question"));
        colCorrectAnswer.setCellValueFactory(new PropertyValueFactory<>("correctAnswer"));
        colScore.setCellValueFactory(new PropertyValueFactory<>("score"));
        colType.setCellValueFactory(new PropertyValueFactory<>("type"));
        chargerQuestions();
    }

    public void chargerQuestions() {
        try {
            masterData = FXCollections.observableArrayList(questionService.afficherQuestion());
            questionsTable.setItems(masterData);
        } catch (SQLException e) {
            ControllerUtils.showError("Erreur lors du chargement des questions : " + e.getMessage());
        }
    }

    @FXML
    private void handleSearch(javafx.event.ActionEvent event) {
        String q = searchTF.getText() == null ? "" : searchTF.getText().trim().toLowerCase();
        if (q.isEmpty()) { questionsTable.setItems(masterData); return; }
        ObservableList<Question> filtered = FXCollections.observableArrayList();
        for (Question item : masterData) {
            boolean matches = false;
            if (String.valueOf(item.getIdQuestion()).contains(q)) matches = true;
            if (!matches && item.getQuestion() != null && item.getQuestion().toLowerCase().contains(q)) matches = true;
            if (!matches && item.getCorrectAnswer() != null && item.getCorrectAnswer().toLowerCase().contains(q)) matches = true;
            if (!matches && String.valueOf(item.getScore()).contains(q)) matches = true;
            if (!matches && item.getType() != null && item.getType().toLowerCase().contains(q)) matches = true;
            if (matches) filtered.add(item);
        }
        questionsTable.setItems(filtered);
    }

    public Question getSelectedQuestion() {
        return questionsTable.getSelectionModel().getSelectedItem();
    }

    @FXML
    private void handleAdd(ActionEvent event) { openFXML(event, "/question_add.fxml"); }

    @FXML
    private void handleEdit(ActionEvent event) {
        if (getSelectedQuestion() == null) { ControllerUtils.showWarning("Sélectionnez une question à modifier."); return; }
        openFXML(event, "/question_edit.fxml");
    }

    @FXML
    private void handleDelete(ActionEvent event) {
        // kept for compatibility with FXML using handleDelete
        supprimerQuestion(event);
    }

    @FXML
    private void supprimerQuestion(ActionEvent event) {
        if (getSelectedQuestion() == null) {
            ControllerUtils.showWarning("Sélectionnez une question à supprimer.");
            return;
        }
        Alert confirm = new Alert(Alert.AlertType.CONFIRMATION, "Supprimer la question sélectionnée ?", javafx.scene.control.ButtonType.YES, javafx.scene.control.ButtonType.NO);
        confirm.showAndWait();
        if (confirm.getResult() == javafx.scene.control.ButtonType.YES) {
            try {
                questionService.supprimerQuestion(getSelectedQuestion().getIdQuestion());
                ControllerUtils.showInfo("Question supprimée.");
                chargerQuestions();
            } catch (SQLException e) {
                ControllerUtils.showError("Erreur lors de la suppression : " + e.getMessage());
            }
        }
    }

    @FXML
    private void handleRefresh(ActionEvent event) { chargerQuestions(); }

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
            ControllerUtils.showError("Impossible de retourner à l'accueil: " + e.getMessage());
        }
    }

    private void openFXML(ActionEvent event, String fxmlPath) {
        try {
            FXMLLoader loader = new FXMLLoader(getClass().getResource(fxmlPath));
            Parent root = loader.load();
            Object controller = loader.getController();
            try {
                if (controller != null) {
                    java.lang.reflect.Method m = null;
                    try { m = controller.getClass().getMethod("setQuestion", Question.class); } catch (NoSuchMethodException ignored) {}
                    if (m != null) m.invoke(controller, getSelectedQuestion());
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
            ControllerUtils.showError("Impossible d'ouvrir la page: " + e.getMessage());
        }
    }
}
