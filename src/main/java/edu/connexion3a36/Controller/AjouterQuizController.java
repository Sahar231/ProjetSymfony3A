package edu.connexion3a36.Controller;

import edu.connexion3a36.entities.Quiz;
import edu.connexion3a36.services.QuizService;
import javafx.fxml.FXML;
import javafx.scene.control.TextField;

import java.sql.SQLException;

public class AjouterQuizController {

    @FXML
    private TextField titreTF;

    private final QuizService quizService = new QuizService();

    @FXML
    public void ajouterQuiz() {
        if (!ControllerUtils.isTextValid(titreTF)) {
            ControllerUtils.showError("Le titre doit être rempli et contenir au moins 6 caractères.");
            return;
        }

        // statut par défaut
        Quiz quiz = new Quiz(titreTF.getText().trim(), "en_attente");

        try {
            quizService.ajouterQuiz(quiz);
            ControllerUtils.showInfo("Quiz ajouté avec succès (en attente de validation).");
            // Retour à la liste avec CSS conservé
            ControllerUtils.navigateTo(titreTF, "/quiz_list.fxml");
        } catch (SQLException e) {
            ControllerUtils.showError("Erreur lors de l'ajout du quiz : " + e.getMessage());
        }
    }

    private void clearFields() {
        titreTF.clear();
    }

    @FXML
    private void handleBack(javafx.event.ActionEvent event) {
        try {
            javafx.fxml.FXMLLoader loader = new javafx.fxml.FXMLLoader(getClass().getResource("/home.fxml"));
            javafx.scene.Parent root = loader.load();
            javafx.scene.Node source = (javafx.scene.Node) event.getSource();
            javafx.stage.Stage stage = (javafx.stage.Stage) source.getScene().getWindow();
            javafx.scene.Scene scene = stage.getScene();
            if (scene == null) {
                javafx.scene.Scene newScene = new javafx.scene.Scene(root, 1100, 700);
                newScene.getStylesheets().add(getClass().getResource("/style.css").toExternalForm());
                stage.setScene(newScene);
            } else {
                scene.setRoot(root);
            }
        } catch (java.io.IOException e) {
            ControllerUtils.showError("Impossible de retourner à l'accueil: " + e.getMessage());
        }
    }
}
